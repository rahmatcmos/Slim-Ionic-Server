<?php
namespace App\Controllers;

use App\Models\Brand;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class BrandController extends Controller
{
	public function getBrand($request,$response)
	{
		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});
		$brandList = Brand::select()->paginate(10);
		$brandList->setPath($this->settings['config']['base_url'].'/dashboard/brand');
		return $this->view->render($response,'brand/manage_brand.twig',['brands'=>$brandList]);
	}

	public function addBrand($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'brand_name' => v::notEmpty()->brandAvailable(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('dashboard.manage.brand'));
		}

		$user = Brand::create([
			'id' => Uuid::uuid4()->toString(),
			'name' => trim($request->getParam('brand_name')),
			'slug' => str_replace(' ','-',trim($request->getParam('brand_name'))),
		]);

		$this->flash->addMessage('info','Brand has been added');

		return $response->withRedirect($this->router->pathFor('dashboard.manage.brand'));
	}

	public function deleteBrand($request,$response)
	{
		$cond = ['id' => $request->getParam('id')];
		if (!Brand::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.brand'));
		}

		$this->flash->addMessage('info','Brand has been deleted');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.brand'));
	}

	public function updateBrand($request,$response)
	{
		$update = Brand::where('id', $request->getParam('id'))
				 ->update(['name' => trim($request->getParam('new_brand_name')),
					   'slug' => str_replace(' ','-',trim($request->getParam('new_brand_name')))]);
		if (!$update) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.brand'));
		}

		$this->flash->addMessage('info','Brand has been updated');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.brand'));
	}
}
