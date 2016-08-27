<?php
namespace App\Controllers\Api;

use App\Models\Wishlist;
use App\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Lcobucci\JWT\Parser;

class WishlistApi extends Controller
{

	public function manageWishlist($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$wishlistList = Wishlist::select('products.id','products.name','products.stock','products.price','products.discount','products.colour','products.size','products.weight','products.photo_1','wishlists.id as wid')
					  ->where('user','=',$uid)
					  ->join('products', 'wishlists.product', '=', 'products.id')
					  ->get();

		return $response->withStatus(200)->withJson(['wishlists'=>$wishlistList]);
	}

	public function deleteWishlist($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$id = ($request->getParam('id')) ? $request->getParam('id') : $postArray['id'];

		if(count($postArray) < 1)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$cond = ['id' => $id, 'user' => $uid];
		if (!Wishlist::where($cond)->delete()) {
			return $response->withStatus(400)->withJson(['message'=>'Fail removing product from wishlist']);
		}

		return $response->withStatus(200)->withJson(['message'=>'Product have been remove from wishlist']);
	}

	public function addWishlist($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$id = ($request->getParam('id')) ? $request->getParam('id') : $postArray['id'];

		if(count($postArray) < 1)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$wishlist = Wishlist::create([
			'id' => Uuid::uuid4()->toString(),
			'user' => $uid,
			'product' => $id,
		]);

		if (!$wishlist) {
			return $response->withStatus(400)->withJson(['message'=>'Fail adding product to wishlist']);
		}

		return $response->withStatus(200)->withJson(['message'=>'Product has been added to wishlist']);
	}

	protected function getJwtUid($request)
	{
		$headers = $request->getHeaders();
		$authorization_token = (String) $headers['HTTP_AUTHORIZATION'][0];
		$token = jwt_bearer_space_splitter($authorization_token);
		$token = (new Parser())->parse((string) $token);
		return $token->getClaim('uid');
	}
}
