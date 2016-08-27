<?php
namespace App\Controllers\Api;

use App\Models\Checkout;
use App\Models\Product;
use App\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Validator as v;
use Lcobucci\JWT\Parser;

class CheckoutApi extends Controller
{

	public function getCheckout($request,$response)
	{
		$uid = $this->getJwtUid($request);
		$checkoutList = Checkout::select('products.name','products.photo_1','checkouts.product', 'checkouts.id','checkouts.amount','checkouts.discount','checkouts.colour','checkouts.size','checkouts.price','checkouts.weight')
					  ->where('checkouts.user',$uid)
					  ->where('checkouts.invoice','=','UNKNOWN')
					  ->join('products', 'checkouts.product', '=', 'products.id')
					  ->get();

		return $response->withStatus(200)->withJson(['checkouts'=>$checkoutList]);
	}

	public function deleteCheckout($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$id = ($request->getParam('id')) ? $request->getParam('id') : $postArray['id'];

		if(count($postArray) < 1)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$cond = ['id' => $id, 'user' => $uid, 'invoice' => 'UNKNOWN'];

		$productCheckout = Checkout::select('amount','product')
			->where($cond)
			->first();

		$product = Product::find($productCheckout->product);
		$product->stock = (int) $product->stock + (int) $productCheckout->amount;
		$product->save();

		if (!Checkout::where($cond)->delete()) {
			return $response->withStatus(400)->withJson(['message'=>'Fail removing product from checkout']);
		}

		return $response->withStatus(200)->withJson(['message'=>'Product have been remove from checkout']);
	}

	public function addCheckout($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$id = ($request->getParam('id')) ? $request->getParam('id') : $postArray['id'];
		$amount = ($request->getParam('amount')) ? $request->getParam('amount') : $postArray['amount'];

		if(count($postArray) < 2)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$validation = $this->validator->validateApi($postArray,[
			'id' => v::notEmpty(),
			'amount' => v::notEmpty()->intVal()->min(1),
		]);

		if ($validation->failed()) {
			return $response->withStatus(400)->withJson($validation->getError());
		}

		$product = Product::select('discount','colour','size','price','weight','stock')
					->where('id', $id)
					->first();

		if (!$product) {
			return $response->withStatus(400)->withJson(['message'=>'Product does not exist']);
		}

		$stock = (int) $product->stock - (int) $amount;
		if($stock < 0)
		{
			return $response->withStatus(400)->withJson(['message'=>'Insufficient product stock']);
		}

		$checkout = Checkout::create([
			'id' => Uuid::uuid4()->toString(),
			'user' => $uid,
			'product' => $id,
			'amount' => $amount,
			'discount' => $product->discount,
			'colour' => $product->colour,
			'size' => $product->size,
			'price' => $product->price,
			'weight' => $product->weight,
		]);

		if (!$checkout) {
			return $response->withStatus(400)->withJson(['message'=>'Could not add product to checkout']);
		}

		$updateProduct = Product::find($id);
		$updateProduct->stock = (int) $stock;
		$updateProduct->save();

		return $response->withStatus(200)->withJson(['message'=>'Product has been added to checkout']);
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
