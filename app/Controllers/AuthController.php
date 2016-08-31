<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Token;
use App\Services\Mailer;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AuthController extends Controller
{

	public function getUser($request,$response)
	{
		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});
		$userList = User::select('id','name','email','role','status','created_at')->where('id','!=',$_SESSION['user'])->paginate(10);
		$userList->setPath($this->settings['config']['base_url'].'/dashboard/member');
		return $this->view->render($response,'auth/manage_user.twig',['users'=>$userList]);
	}

	public function deleteUser($request,$response)
	{
		$cond = ['id' => $request->getParam('id')];
		if (!User::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		$this->flash->addMessage('info','User has been deleted');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.user'));
	}

	public function updateUser($request,$response)
	{
		$update = User::where('id', $request->getParam('id'))
				->update(['status' => $request->getParam('status'),'role' => $request->getParam('role')]);
		if (!$update) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		$this->flash->addMessage('info','User has been updated');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.user'));
	}

	public function getSignOut($request,$response)
	{
		$this->auth->logout();
		$this->flash->addMessage('info','You have been logged out');
		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getSignIn($request,$response)
	{
		return $this->view->render($response,'auth/signin.twig');
	}

	public function postSignIn($request,$response)
	{
		$auth = $this->auth->attempt(
			$request->getParam('email'),
			$request->getParam('password')
		);

		if (!$auth) {
			$this->flash->addMessage('error','Could not sign you in with those details');
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}

		if ($auth === "BAN" || $auth === "INACTIVE") {
			$this->flash->addMessage('error','Could not sign::'.$auth);
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}

		Token::where('user',$auth->id)->delete();

		$this->flash->addMessage('info','You have been logged in');
		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getSignUp($request,$response)
	{
		return $this->view->render($response,'auth/signup.twig');
	}

	public function postSignUp($request,$response)
	{

		$validation = $this->validator->validate($request,[
			'email' => v::noWhitespace()->notEmpty()->email()->emailAvailable(),
			'name' => v::noWhitespace()->notEmpty()->alnum()->length(8)->usernameAvailable(),
			'password' => v::noWhitespace()->notEmpty()->length(8),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}

		$user = User::create([
			'id' => Uuid::uuid4()->toString(),
			'email' => $request->getParam('email'),
			'name' => $request->getParam('name'),
			'password' => password_hash($request->getParam('password'),PASSWORD_DEFAULT),
		]);

		$this->flash->addMessage('info','You have been signed up');

		$this->auth->attempt($user->email,$request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getChangePassword($request,$response)
	{
		return $this->view->render($response,'auth/change.twig');
	}

	public function postChangePassword($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'password_old' => v::noWhitespace()->notEmpty()->matchesPassword($this->auth->user()->password),
			'password' => v::noWhitespace()->notEmpty()->length(8),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.password.change'));
		}

		$this->auth->user()->setPassword($request->getParam('password'));

		$this->flash->addMessage('info','Your password was changed');

		return $response->withRedirect($this->router->pathFor('home'));

	}

	public function getResetPassword($request,$response)
	{
		return $this->view->render($response,'auth/reset.twig');
	}

	public function postResetPassword($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'email' => v::noWhitespace()->notEmpty()->email()->existEmail(),
			]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.reset'));
		}

		$user = User::where('email',$request->getParam('email'))->first();
		$token = Token::create(['id' => Uuid::uuid4()->toString(), 'user' => $user->id]);
		if($token)
		{
			Token::where('user',$user->id)->where('id','!=',$token->id)->delete();
		}

		$title = 'Recovery Token';
		$body = $this->view->render($response,'emailsTemplates/forgot_password.twig', ['user'=>$user, 'token'=>$token])->getBody();
		$altBody = 'This is your recovery token:: expired 3 hour after '.$token->created_at."\r\n".$this->settings['config']['base_url'].'/auth/recover?token='.$token->id;
		$mailer = new Mailer($this->settings);
		$result = $mailer->addRecipient($user)->addMessage($title, $body, $altBody, true)->init();

		if($result !== true)
			$this->flash->addMessage('error','Unable to deliver instruction to mail box');
		else
			$this->flash->addMessage('info','Check instruction in mail(spam) box');

		return $response->withRedirect($this->router->pathFor('home'));
	}

	public function getRecoverAccount($request,$response)
	{
		$tokenParam = $request->getParam('token');
		if(!$tokenParam)
		{
			$this->flash->addMessage('error','Recovery token does not valid');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		$token = Token::find($tokenParam);

		if(!$token)
		{
			$this->flash->addMessage('error','Recovery token does not valid');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		$bal_time = Carbon::now()->subHour(2);

		if($bal_time > $token->created_at)
		{
			$this->flash->addMessage('error','Recovery token has expired');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		return $this->view->render($response,'auth/recover.twig',['token'=>$token]);
	}

	public function postRecoverAccount($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'password' => v::noWhitespace()->notEmpty()->length(8)->matchesValue($request->getParam('confirm_password')),
			'confirm_password' => v::noWhitespace()->notEmpty()->length(8)->matchesValue($request->getParam('password')),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.recover').'?token='.$request->getParam('token'));
		}

		User::find($request->getParam('user'))->setPassword($request->getParam('password'));
		Token::where('user',$request->getParam('user'))->delete();

		$this->flash->addMessage('info','Password has been changed');
		return $response->withRedirect($this->router->pathFor('auth.signin'));
	}
}
