<?php
namespace App\Controllers\Api;

use App\Models\User;
use App\Models\Jwt;
use Carbon\Carbon;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Lcobucci\JWT\Parser;
use Slim\Http\MobileRequest;

class JwtApi extends Controller
{

	public function signIn($request,$response)
	{
		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$email = ($request->getParam('email')) ? $request->getParam('email') : $postArray['email'];
		$password = ($request->getParam('password')) ? $request->getParam('password') : $postArray['password'];

		if(count($postArray) < 2)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$jti = Uuid::uuid4()->toString();
		$device = 'Unknown';
		$os = 'Unknown';
		$request = new MobileRequest($request);

		if($request->isMobile() && !$request->isTablet())
		{
			$device = 'Mobile';
		}
		elseif($request->isTablet())
		{
			$device = 'Tablet';
		}

		if($request->isiOS())
		{
			$os = 'IOS';
		}
		elseif($request->isAndroidOS())
		{
			$os = 'Android';
		}

		$time = Carbon::now();
		$micro = $time->timestamp;
		$issued_at = $time->toDateTimeString();
		$not_before = $time->subSecond($this->settings['jwt']['notBefore'])->toDateTimeString();
		$expired_at = $time->addSeconds($this->settings['jwt']['expired'])->toDateTimeString();

		$token = $this->jwtauth->requestJWT(
			$email,
			$password,
			$micro,
			$this->settings['jwt']['issuer'],
			$device,
			$jti,
			$this->settings['jwt']['notBefore'],
			$this->settings['jwt']['expired'],
			$this->settings['jwt']['secret'],
			$expired_at,
			$os
		);

		if (!$token || $token == "BAN" || $token == "INACTIVE") {
			$reason = ($token !== false) ? $token : 'Fail';
			return $response->withStatus(400)->withJson(['reason'=>$reason]);
		}

		return $response->withStatus(200)->withJson(['id_token'=>$token]);
	}

	public function signUp($request,$response)
	{
		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$name = ($request->getParam('name')) ? $request->getParam('name') : $postArray['name'];
		$email = ($request->getParam('email')) ? $request->getParam('email') : $postArray['email'];
		$password = ($request->getParam('password')) ? $request->getParam('password') : $postArray['password'];

		if(count($postArray) < 3)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$validation = $this->validator->validateApi($postArray,[
			'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
			'name' => v::noWhitespace()->notEmpty()->alnum()->length(8)->usernameAvailable(),
			'password' => v::noWhitespace()->notEmpty()->length(8),
		]);

		if ($validation->failed()) {
			return $response->withStatus(400)->withJson($validation->getError());
		}

		$user = User::create([
			'id' => Uuid::uuid4()->toString(),
			'email' => $email,
			'name' => $name,
			'password' => password_hash($password,PASSWORD_DEFAULT),
		]);

		return $response->withStatus(200)->withJson(['info'=>'You have been signed up']);
	}

	public function signOut($request,$response)
	{
		$headers = $request->getHeaders();
		$authorization_token = (String) $headers['HTTP_AUTHORIZATION'][0];
		$token = jwt_bearer_space_splitter($authorization_token);
		$token = (new Parser())->parse((string) $token);
		$jti = $token->getHeader('jti');
		Jwt::where('id', $jti)->delete();
		return $response->withStatus(200)->withJson(['logout'=>$jti]);
	}
}
