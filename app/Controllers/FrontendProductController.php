<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Controllers\Controller;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class FrontendProductController extends Controller
{

	public function listProduct($request,$response)
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

		$productList->setPath($this->settings['config']['base_url'].'/product');

		$categoryList = Category::select('slug','name')->get();
		$brandList = Brand::select('slug','name')->get();
		$data = ['categories'=>$categoryList, 'pageTitle'=>'Product', 'brands'=>$brandList, 'products'=>$productList];
		return $this->view->render($response,'product/frontend/list_product.twig',$data);
	}

	public function searchProduct($request,$response)
	{
		$currentKeyword = (isset($_GET['keyword']) ? $_GET['keyword'] : false);
		$currentBrand = (isset($_GET['brand']) ? $_GET['brand'] : false);
		$currentCategory = (isset($_GET['category']) ? $_GET['category'] : false);
		$currentOrderby = (isset($_GET['order_by']) ? $_GET['order_by'] : 'name');
		$currentOrdering = (isset($_GET['ordering']) ? $_GET['ordering'] : 'asc');

		if (!$currentKeyword && !$currentBrand && !$currentCategory) {
			return $response->withRedirect($this->router->pathFor('frontend.list.product'));
		}

		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		if($currentKeyword)
		{
			if($currentBrand && $currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('products.name', 'like', '%'.$currentKeyword.'%')
					->where('brands.slug', $currentBrand)
					->where('categories.slug', $currentCategory)
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?keyword='.$currentKeyword.'&brand='.$currentBrand.'&category='.$currentCategory.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
			elseif(!$currentBrand && $currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('products.name', 'like', '%'.$currentKeyword.'%')
					->where('categories.slug', $currentCategory)
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?keyword='.$currentKeyword.'&category='.$currentCategory.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
			elseif($currentBrand && !$currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('products.name', 'like', '%'.$currentKeyword.'%')
					->where('brands.slug', $currentBrand)
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?keyword='.$currentKeyword.'&brand='.$currentBrand.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
			elseif(!$currentBrand && !$currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('products.name', 'like', '%'.$currentKeyword.'%')
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?keyword='.$currentKeyword.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
		}
		elseif(!$currentKeyword)
		{
			if($currentBrand && $currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('brands.slug', $currentBrand)
					->where('categories.slug', $currentCategory)
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?brand='.$currentBrand.'&category='.$currentCategory.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
			elseif(!$currentBrand && $currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('categories.slug', $currentCategory)
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?category='.$currentCategory.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
			elseif($currentBrand && !$currentCategory)
			{
				$productList = Product::select('products.id','products.name','products.stock','products.price','products.discount','products.weight','categories.name as category','brands.name as brand')
					->where('brands.slug', $currentBrand)
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->orderBy($currentOrderby, $currentOrdering)
					->paginate(10);
				$productList->setPath($this->settings['config']['base_url'].'/product/search?brand='.$currentBrand.'&order_by='.$currentOrderby.'&ordering='.$currentOrdering);
			}
		}

		$categoryList = Category::select('slug','name')->get();
		$brandList = Brand::select('slug','name')->get();
		$data = ['categories'=>$categoryList, 'pageTitle'=>'Search Product', 'brands'=>$brandList, 'products'=>$productList];
		return $this->view->render($response,'product/frontend/list_product.twig',$data);
	}

	public function viewProduct($request,$response)
	{
		$productData = Product::select('products.id','products.name','products.stock','products.price','products.detail','products.discount','products.colour','products.size','products.photo_1','products.photo_2','products.photo_3','products.weight','categories.name as category','brands.name as brand')
					->where('products.id', $request->getParam('id'))
					->join('categories', 'products.category', '=', 'categories.id')
					->join('brands', 'products.brand', '=', 'brands.id')
					->first();

		if (!$productData) {
			$this->flash->addMessage('error','Product does not exist');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.product'));
		}

		return $this->view->render($response,'product/frontend/view_product.twig', ['product'=>$productData,'pageTitle'=>$productData->name]);
	}
}
