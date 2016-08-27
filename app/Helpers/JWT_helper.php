<?php

if ( ! function_exists('jwt_bearer_splitter'))
{
	function jwt_bearer_space_splitter($token) //Bearer tokenstringhere 
	{
		$splitted = explode(' ', $token);
		if(is_array($splitted))
			return $splitted[1]; //return xxx.yyy.zzz
		return '';
	}
}

if ( ! function_exists('is_jwt_token_valid'))
{
	function is_jwt_token_valid($token) //xxx.yyy.zzz
	{
		$splitted = explode('.', $token);
		if(!is_array($splitted) || count($splitted) !== 3)
			return TRUE;
		return FALSE;
	}
}
