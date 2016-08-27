<?php

namespace App\Middleware;

/**
* 
*/
class OldInputMiddleware extends Middleware
{
	public function __invoke($request,$response,$next)
	{
		$_SESSION['old'] = $request->getParams();
		$response = $next($request,$response);
		return $response;
	}
}
