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

if ( ! function_exists('breadcrumb'))
{
	function breadcrumb()
	{
		$concatLi = '';
		$concatLink = '';

		$liCollection = [];
		$liCollection[''] = 'Home';
		$request_uri = explode('/',$_SERVER['REQUEST_URI']);
		$request_uri = array_splice($request_uri, 1);

		foreach($request_uri as $index => $segment)
		{
			if($segment !== '')
				$liCollection[$segment] = ucfirst($segment);
		}

		end($liCollection);
		$lastIndex = key($liCollection);

		$i = 0;
		foreach($liCollection as $index => $segment)
		{
			if($i !== 1)
				$concatLink .= '/'.$index;
			else
				$concatLink .= $index;

			if($lastIndex === $index)
				$concatLi .= '<li class="active">'.$segment.'</li>';
			else
				$concatLi .= '<li><a href="'.$concatLink.'">'.$segment.'</a></li>';
			$i++;
		}

		return '<ol class="breadcrumb">'.$concatLi.'</ol>';
	}
}
