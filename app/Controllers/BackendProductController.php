<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
use Ramsey\Uuid\Uuid;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use App\Uploader\Uploader;

class BackendProductController extends Controller
{

	public function getProduct($request,$response)
	{
		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy('name', 'asc')
					->paginate(10);

		$productList->setPath($this->settings['config']['base_url'].'/dashboard/product');
		return $this->view->render($response,'product/backend/manage_product.twig',['products'=>$productList,'pageTitle'=>'Manage Product']);
	}

	public function searchProduct($request,$response)
	{
		$currentKeyword = (isset($_GET['keyword']) ? $_GET['keyword'] : false);
		if (!$currentKeyword) {
			return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
		}

		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('products.name', 'like', '%'.$currentKeyword.'%')
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy('name', 'asc')
					->paginate(10);

		$productList->setPath($this->settings['config']['base_url'].'/dashboard/product/search?keyword='.$currentKeyword);
		return $this->view->render($response,'product/backend/manage_product.twig',['products'=>$productList,'pageTitle'=>'Search Product']);
	}

	public function getAddProduct($request,$response)
	{
		$categoryList = Category::select('id','name')->get();
		$brandList = Brand::select('id','name')->get();
		$data = ['categories'=>$categoryList, 'brands'=>$brandList,'pageTitle'=>'Add Product'];
		return $this->view->render($response,'product/backend/add_product.twig', $data);
	}

	public function postAddProduct($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'name' => v::notEmpty(),
			'stock' => v::notEmpty()->intVal()->min(1),
			'price' => v::notEmpty()->floatVal()->min(1),
			'discount' => v::intVal()->max(99),
			'detail' => v::notEmpty(),
			'weight' => v::notEmpty()->floatVal()->min(1),
			'category' => v::notEmpty()->categoryExist(),
			'brand' => v::notEmpty()->brandExist(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('dashboard.add.product'));
		}

		$d = new Uploader(['uploadPath'=>BASEPATH.'/public/assets/image/',
				   'createThumb'=>true,
				   'fieldName'=>'photo',
				   'required'=>[false,false,false],
				   'allowedExt'=>'jpg|png',
				   'minWidth' => 600,
				   'minHeight' => 400]);

		if($d->multiUpload()->uploadError())
		{
			foreach($d->getError() as $field => $error)
			{
				$_SESSION['errors'][$field] = $error;
			}
			return $response->withRedirect($this->router->pathFor('dashboard.add.product'));
		}
		$uploadFile = $d->getSuccess();
		//var_dump($uploadFile);
		//var_dump(!empty($uploadFile['photo_0']['saveFile']) ? $uploadFile['photo_0']['saveFile'] : 'default.jpg');
		//exit();
		$uid = Uuid::uuid4()->toString();
		$product = Product::create([
			'id' => $uid,
			'name' => trim($request->getParam('name')),
			'slug' => str_replace(' ','-',trim($request->getParam('name'))).'-'.$uid,
			'detail' => $request->getParam('detail'),
			'stock' => $request->getParam('stock'),
			'price' => $request->getParam('price'),
			'discount' => empty($request->getParam('discount')) ? '0' : $request->getParam('discount'),
			'colour' => empty($request->getParam('colour')) ? 'UNKNOWN' : $request->getParam('colour'),
			'size' => empty($request->getParam('size')) ? 'UNKNOWN' : $request->getParam('size'),
			'weight' => $request->getParam('weight'),
			'category' => $request->getParam('category'),
			'brand' => $request->getParam('brand'),
			'photo_1' => !empty($uploadFile['photo_0']['saveFile']) ? $uploadFile['photo_0']['saveFile'] : 'default.jpg',
			'photo_2' => !empty($uploadFile['photo_1']['saveFile']) ? $uploadFile['photo_1']['saveFile'] : 'default.jpg',
			'photo_3' => !empty($uploadFile['photo_2']['saveFile']) ? $uploadFile['photo_2']['saveFile'] : 'default.jpg',
		]);

		$this->flash->addMessage('info','Product has been added');

		return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
	}

	public function deleteProduct($request,$response)
	{
		$cond = ['id' => $request->getParam('id')];
		if (!Product::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
		}

		$this->flash->addMessage('info','Product has been deleted');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
	}

	public function getUpdateProduct($request,$response)
	{
		$productData = Product::find($request->getParam('id'));
		if (!$productData) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
		}

		$categoryList = Category::select('id','name')->get();
		$brandList = Brand::select('id','name')->get();
		$data = ['categories'=>$categoryList, 'pageTitle'=>$productData->name, 'brands'=>$brandList, 'product'=>$productData];
		return $this->view->render($response,'product/backend/update_product.twig', $data);
	}

	public function postUpdateProduct($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'name' => v::notEmpty(),
			'stock' => v::notEmpty()->intVal()->min(1),
			'price' => v::notEmpty()->floatVal()->min(1),
			'discount' => v::intVal()->max(99),
			'detail' => v::notEmpty(),
			'weight' => v::notEmpty()->floatVal()->min(1),
			'category' => v::notEmpty()->categoryExist(),
			'brand' => v::notEmpty()->brandExist(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('dashboard.update.product').'?id='.$request->getParam('id'));
		}

		$d = new Uploader(['uploadPath'=>BASEPATH.'/public/assets/image/',
				   'createThumb'=>true,
				   'fieldName'=>'photo',
				   'required'=>[false,false,false],
				   'allowedExt'=>'jpg|png',
				   'minWidth' => 600,
				   'minHeight' => 400]);

		if($d->multiUpload()->uploadError())
		{
			foreach($d->getError() as $field => $error)
			{
				$_SESSION['errors'][$field] = $error;
			}
			return $response->withRedirect($this->router->pathFor('dashboard.add.product'));
		}
		$uploadFile = $d->getSuccess();

		$update = Product::where('id', $request->getParam('id'))->update(['name' => trim($request->getParam('name')),
										   'stock' => $request->getParam('stock'),
										   'price' => $request->getParam('price'),
										   'discount' => empty($request->getParam('discount')) ? '0' : $request->getParam('discount'),
										   'detail' => $request->getParam('detail'),
										   'colour' => empty($request->getParam('colour')) ? 'UNKNOWN' : $request->getParam('colour'),
										   'size' => empty($request->getParam('size')) ? 'UNKNOWN' : $request->getParam('size'),
										   'weight' => $request->getParam('weight'),
										   'category' => $request->getParam('category'),
										   'brand' => $request->getParam('brand'),
										   'photo_1' => !empty($uploadFile['photo_0']['saveFile']) ? $uploadFile['photo_0']['saveFile'] : $request->getParam('photo_1'),
										   'photo_2' => !empty($uploadFile['photo_1']['saveFile']) ? $uploadFile['photo_1']['saveFile'] : $request->getParam('photo_2'),
										   'photo_3' => !empty($uploadFile['photo_2']['saveFile']) ? $uploadFile['photo_2']['saveFile'] : $request->getParam('photo_3'),
										   ]);
		if (!$update) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
		}

		$this->flash->addMessage('info','Product has been updated');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
	}
}
