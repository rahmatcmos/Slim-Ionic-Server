<?php
namespace App\Controllers\Api;

use App\Controllers\Controller;

class InformationApi extends Controller
{

	public function getCompanyInfo($request,$response)
	{
		return $response->withStatus(200)->withJson($this->settings['shipping']);
	}
}
