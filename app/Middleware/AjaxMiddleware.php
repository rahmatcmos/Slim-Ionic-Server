<?php

namespace App\Middleware;

/**
* 
*/
class AjaxMiddleware extends Middleware
{
	
	public function __invoke($request,$response,$next)
	{
		if(!$request->isXhr()) 
		{
			$body = $response->getBody();
			$body->write('XHR/Ajax only!');
			return $response->withStatus(400)->withHeader('Content-type', 'text/plain')->withBody($body);
		}
		$response = $next($request,$response);
		return $response;
	}
}
