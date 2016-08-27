<?php
namespace App\Controllers;

use App\Models\Wishlist;
use App\Controllers\Controller;
use Ramsey\Uuid\Uuid;

class WishlistController extends Controller
{

	public function getWishlist($request,$response)
	{
		$wishlistList = Wishlist::select('products.id','products.name','products.stock','products.price','wishlists.id as wid')
					  ->where('user','=',$_SESSION['user'])
					  ->join('products', 'wishlists.product', '=', 'products.id')
					  ->get();

		return $this->view->render($response,'wishlist/wishlist.twig',['wishlists'=>$wishlistList]);
	}

	public function deleteWishlist($request,$response)
	{
		$cond = ['id' => $request->getParam('id'), 'user' => $_SESSION['user']];
		if (!Wishlist::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('wishlist.index'));
		}

		$this->flash->addMessage('info','Wishlist has been deleted');
		return $response->withRedirect($this->router->pathFor('wishlist.index'));
	}

	public function addWishlist($request,$response)
	{
		$wishlist = Wishlist::create([
			'id' => Uuid::uuid4()->toString(),
			'user' => $_SESSION['user'],
			'product' => $request->getParam('id'),
		]);

		if (!$wishlist) {
			$this->flash->addMessage('error','Could not add product into wishlist');
			return $response->withRedirect($this->router->pathFor('frontend.view.product').'?id='.$request->getParam('id'));
		}

		$this->flash->addMessage('info','Product has been added into wishlist');
		return $response->withRedirect($this->router->pathFor('frontend.view.product').'?id='.$request->getParam('id'));
	}
}
