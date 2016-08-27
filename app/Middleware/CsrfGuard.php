<?php

namespace App\Middleware;

use ArrayAccess;
use Countable;
use Traversable;
use IteratorAggregate;
use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Csrf\Guard;

class CsrfGuard extends Guard
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
	{
		// Validate POST, PUT, DELETE, PATCH requests
		if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
			if(strtolower(uri_segment(1)) !== 'api')
			{
				$body = $request->getParsedBody();
				$body = $body ? (array)$body : [];
				$name = isset($body[$this->prefix . '_name']) ? $body[$this->prefix . '_name'] : false;
				$value = isset($body[$this->prefix . '_value']) ? $body[$this->prefix . '_value'] : false;
				if (!$name || !$value || !$this->validateToken($name, $value)) {
					// Need to regenerate a new token, as the validateToken removed the current one.
					$request = $this->generateNewToken($request);

					$failureCallable = $this->getFailureCallable();
					return $failureCallable($request, $response, $next);
				}
			}
		}
		else {
			// Clear previous CSRF token(GET), prevent csrf from being harvested
			if(isset($_SESSION['old_csrf']))
				unset($_SESSION[$this->prefix][$_SESSION['old_csrf']]);
		}

		// Generate new CSRF token
		$request = $this->generateNewToken($request);

		// Set current CSRF token as old
		$_SESSION['old_csrf'] = $this->getTokenName();

		// Enforce the storage limit
		$this->enforceStorageLimit();

		return $next($request, $response);
	}
}
