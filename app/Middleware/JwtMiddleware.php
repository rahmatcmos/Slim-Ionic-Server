<?php

namespace App\Middleware;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
/**
* 
*/
class JwtMiddleware extends Middleware
{

	public function __invoke($request,$response,$next)
	{
		$body = $response->getBody();
		$headers = $request->getHeaders();

		if(!isset($headers['HTTP_AUTHORIZATION'])) 
		{
			$body->write('JWT Not Present');
			return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
		}

		$authorization_token = (String) $headers['HTTP_AUTHORIZATION'][0];

		if(is_jwt_token_valid(jwt_bearer_space_splitter($authorization_token)))
		{
			$body->write('JWT Format Incorrect');
			return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
		}

		$token = jwt_bearer_space_splitter($authorization_token);
		$token = (new Parser())->parse((string) $token);
		$signer = new Sha256();
		$jti = $token->getHeader('jti');
		$aud = $token->getClaim('aud');

		if(!$this->container->jwtauth->checkJWT($jti))
		{
			$body->write('JWT Not Exist');
			return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
		}

		if(!$token->verify($signer, $this->container->settings['jwt']['secret']))
		{
			$body->write('JWT Invalid');
			return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
		}

		$data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
		$data->setIssuer($this->container->settings['jwt']['issuer']);
		$data->setAudience($aud);
		$data->setId($jti);
		if(!$token->validate($data))
		{
			$body->write('JWT Expired');
			return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
		}

		$response = $next($request,$response);
		return $response;
	}
}
