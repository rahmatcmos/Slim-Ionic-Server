<?php

namespace App\Controllers;

use App\Models\Jwt;

class JwtController extends Controller
{
	public function getJwt($request,$response)
	{
		$jwtList = Jwt::where('user', $_SESSION['user'])->get();
		return $this->view->render($response,'jwt.twig',['jwts'=>$jwtList,'pageTitle'=>'Manage JWT']);
	}

	public function deleteJwt($request,$response)
	{
		$cond = ['id' => $request->getParam('jti'), 'user'=> $_SESSION['user']];
		if (!Jwt::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('jwt.index'));
		}

		$this->flash->addMessage('info','JWT token has been deleted');
		return $response->withRedirect($this->router->pathFor('jwt.index'));
	}
}
