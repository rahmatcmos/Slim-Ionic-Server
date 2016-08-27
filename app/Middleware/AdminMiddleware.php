<?php

namespace App\Middleware;

/**
* 
*/
class AdminMiddleware extends Middleware
{
	public function __invoke($request,$response,$next)
	{
		$user = $this->container->auth->user();
		$role = isset($user) ? $user->role : null;
		if($role !== 'ADMIN' || !$this->container->auth->check()) {
			$this->container->flash->addMessage('error','Access denied');
			return $response->withRedirect($this->container->router->pathFor('home'));
		}
		
		$response = $next($request,$response);
		return $response;
	}
}
