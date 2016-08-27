<?php

if ( ! function_exists('uri_segment'))
{
	function uri_segment($segment = 0)
	{
		$request_uri = explode('/',$_SERVER['REQUEST_URI']);
		$request_uri = array_splice($request_uri, 1);
		if(count(explode('.',$request_uri[0])) > 1) {
			$request_uri = array_splice($request_uri, 1);
		}
		return isset($request_uri[$segment-1]) ? $request_uri[$segment-1] : null;
	}
}
