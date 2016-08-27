<?php
namespace App\Controllers;

use App\Models\Category;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class CategoryController extends Controller
{
	public function getCategory($request,$response)
	{
		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});
		$categoryList = Category::select()->paginate(10);
		$categoryList->setPath($this->settings['config']['base_url'].'/dashboard/category');
		return $this->view->render($response,'category/manage_category.twig',['categories'=>$categoryList]);
	}

	public function addCategory($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'category_name' => v::notEmpty()->categoryAvailable(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
		}

		$user = Category::create([
			'id' => Uuid::uuid4()->toString(),
			'name' => trim($request->getParam('category_name')),
			'slug' => str_replace(' ','-',trim($request->getParam('category_name'))),
		]);

		$this->flash->addMessage('info','Category has been added');

		return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
	}

	public function deleteCategory($request,$response)
	{
		$cond = ['id' => $request->getParam('id')];
		if (!Category::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
		}

		$this->flash->addMessage('info','Category has been deleted');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
	}

	public function updateCategory($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'new_category_name' => v::notEmpty()->categoryAvailable(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
		}

		$update = Category::where('id', $request->getParam('id'))
				    ->update(['name' => trim($request->getParam('new_category_name')),
					      'slug' => str_replace(' ','-',trim($request->getParam('new_category_name')))]);

		if (!$update) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
		}

		$this->flash->addMessage('info','Category has been updated');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.category'));
	}
}
