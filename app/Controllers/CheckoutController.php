<?php
namespace App\Controllers;

use App\Models\Checkout;
use App\Models\Product;
use App\Controllers\Controller;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Validator as v;

class CheckoutController extends Controller
{

	public function getCheckout($request,$response)
	{
		$checkoutList = Checkout::select('products.name','checkouts.id','checkouts.product','checkouts.amount','checkouts.discount','checkouts.colour','checkouts.size','checkouts.price','checkouts.weight')
					  ->where('checkouts.user',$_SESSION['user'])
					  ->where('checkouts.invoice','UNKNOWN')
					  ->join('products', 'checkouts.product', '=', 'products.id')
					  ->get();

		return $this->view->render($response,'checkout/checkout.twig',['checkouts'=>$checkoutList,'pageTitle'=>'Checkout']);
	}

	public function deleteCheckout($request,$response)
	{
		$cond = ['id' => $request->getParam('id'), 'user' => $_SESSION['user'], 'invoice' => 'UNKNOWN'];

		$productCheckout = Checkout::select('amount','product')
			->where($cond)
			->first();

		$product = Product::find($productCheckout->product);
		$product->stock = (int) $product->stock + (int) $productCheckout->amount;
		$product->save();

		if (!Checkout::where($cond)->delete()) {
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('checkout.index'));
		}

		$this->flash->addMessage('info','Checkout has been deleted');
		return $response->withRedirect($this->router->pathFor('checkout.index'));
	}

	public function addCheckout($request,$response)
	{
		$validation = $this->validator->validate($request,[
			'id' => v::notEmpty(),
			'amount' => v::notEmpty()->intVal()->min(1),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('frontend.view.product').'?id='.$request->getParam('id'));
		}

		$product = Product::select('discount','colour','size','price','weight','stock')
					->where('id', $request->getParam('id'))
					->first();

		if (!$product) {
			$this->flash->addMessage('error','Product does not exist');
			return $response->withRedirect($this->router->pathFor('home'));
		}

		$stock = (int) $product->stock - (int) $request->getParam('amount');
		if($stock < 0)
		{
			$this->flash->addMessage('error','Product out of stock');
			return $response->withRedirect($this->router->pathFor('frontend.view.product').'?id='.$request->getParam('id'));
		}

		$checkout = Checkout::create([
			'id' => Uuid::uuid4()->toString(),
			'user' => $_SESSION['user'],
			'product' => $request->getParam('id'),
			'amount' => $request->getParam('amount'),
			'discount' => $product->discount,
			'colour' => $product->colour,
			'size' => $product->size,
			'price' => $product->price,
			'weight' => $product->weight,
		]);

		if (!$checkout) {
			$this->flash->addMessage('error','Could not add product into checkout');
			return $response->withRedirect($this->router->pathFor('frontend.view.product').'?id='.$request->getParam('id'));
		}

		$updateProduct = Product::find($request->getParam('id'));
		$updateProduct->stock = (int) $stock;
		$updateProduct->save();

		$this->flash->addMessage('info','Product has been added into checkout');
		return $response->withRedirect($this->router->pathFor('checkout.index'));
	}
}
