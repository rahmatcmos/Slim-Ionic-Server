<?php
namespace App\Controllers\Api;

use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\Shipping;
use App\Models\Product;
use App\Controllers\Controller;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Validator as v;
use Lcobucci\JWT\Parser;

class InvoiceApi extends Controller
{

	public function getInvoice($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$invoiceList = Invoice::select('id','total_amount','shipping','total_price','total_weight','status','created_at')
					->where('user',$uid)
					->orderBy('created_at', 'desc')
					->paginate(4);

		$invoiceList->setPath('/v1/invoice');
		return $response->withStatus(200)->withJson(['invoices'=>$invoiceList]);
	}

	public function viewInvoice($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$cond = ['id' => $_GET['id'], 'user' => $uid];
		$invoice = Invoice::select('id','user','billing','mobile','total_amount','total_price','total_weight','status','collector','created_at')->where($cond)->first();

		if(!$invoice) 
		{
			return $response->withStatus(400)->withJson(['message'=>'Problem viewing invoice']);
		}

		$invoice->checkoutProducts;
		$invoice->shippingRecord;

		return $response->withStatus(200)->withJson(['invoice'=>$invoice]);
	}

	public function deleteInvoice($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		$id = ($request->getParam('id')) ? $request->getParam('id') : $postArray['id'];

		if(count($postArray) < 1)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		if(Invoice::where(['id' => $id, 'user' => $uid, 'status' => 0])->delete())
		{
			Shipping::where('invoice', $id)->delete();

			$cond = ['invoice' => $id, 'user' => $uid];
			$productCheckouts = Checkout::select('amount','product')->where($cond)->get();

			foreach($productCheckouts as $index => $productData)
			{
				$product = Product::find($productData->product);
				$product->stock = (int) $product->stock + (int) $productData->amount;
				$product->save();
			}

			Checkout::where('invoice', $id)->delete();
			return $response->withStatus(200)->withJson(['message'=>'Invoice has been deleted']);
		}

		return $response->withStatus(400)->withJson(['message'=>'Fail deleting invoice']);
	}

	public function generateInvoice($request,$response)
	{
		$uid = $this->getJwtUid($request);

		$post = json_decode($request->getBody());
		$postArray = get_object_vars($post);

		if(count($postArray) < 9)
		{
			return $response->withStatus(400)->withJson(['message'=>'Missing form value']);
		}

		$recipient = ($request->getParam('recipient')) ? $request->getParam('recipient') : $postArray['recipient'];
		$first_address = ($request->getParam('first_address')) ? $request->getParam('first_address') : $postArray['first_address'];
		$second_address = ($request->getParam('second_address')) ? $request->getParam('second_address') : $postArray['second_address'];
		$poscode = ($request->getParam('poscode')) ? $request->getParam('poscode') : $postArray['poscode'];
		$city = ($request->getParam('city')) ? $request->getParam('city') : $postArray['city'];
		$state = ($request->getParam('state')) ? $request->getParam('state') : $postArray['state'];
		$billing = ($request->getParam('billing')) ? $request->getParam('billing') : $postArray['billing'];
		$mobile = ($request->getParam('mobile')) ? $request->getParam('mobile') : $postArray['mobile'];
		$selfpickup = ($request->getParam('selfpickup')) ? $request->getParam('selfpickup') : $postArray['selfpickup'];

		$totalAmount = 0;
		$totalPrice = 0;
		$totalWeight = 0;
		$rateFirst500g = 0;
		$rateNext250g = 0;
		$shippingCost = 0;
		$shippingID = 'selfpickup';
		$invoiceID = Uuid::uuid4()->toString();

		if($selfpickup)
		{
			$validation = $this->validator->validateApi($postArray,[
				'billing' => v::notEmpty(),
				'mobile' => v::notEmpty()->phone()->length(10,13),
			]);

			if ($validation->failed()) {
				return $response->withStatus(400)->withJson($validation->getError());
			}
		}
		else
		{
			$validation = $this->validator->validateApi($postArray,[
				'billing' => v::notEmpty(),
				'mobile' => v::notEmpty()->phone()->length(10,13),
				'recipient' => v::notEmpty(),
				'first_address' => v::notEmpty(),
				//'second_address' => v::notEmpty(),
				'poscode' => v::notEmpty()->PostalCode('MY'),
				'city' => v::notEmpty(),
				'state' => v::notEmpty(),
			]);

			if ($validation->failed()) {
				return $response->withStatus(400)->withJson($validation->getError());
			}

			if((int) $poscode === $this->settings['shipping']['self_poscode'])
			{
				//local 4 0.8
				$rateFirst500g = 4;
				$rateNext250g = 0.8;
			}
			elseif(in_array($state, $this->settings['shipping']['zone_1']))
			{
				$recipientZone = 'zone_1';
				if($recipientZone === $this->settings['shipping']['self_zone'])
				{
					//1:1 4.5 1
					$rateFirst500g = 4.5;
					$rateNext250g = 1;
				}
				elseif($this->settings['shipping']['self_zone'] === 'zone_2')
				{
					//1:2 6.5 1.5
					$rateFirst500g = 6.5;
					$rateNext250g = 1.5;
				}
				elseif($this->settings['shipping']['self_zone'] === 'zone_3')
				{
					//1:3 7 2
					$rateFirst500g = 7;
					$rateNext250g = 2;
				}
			}
			elseif(in_array($state, $this->settings['shipping']['zone_2']))
			{
				$recipientZone = 'zone_2';
				if($recipientZone === $this->settings['shipping']['self_zone'])
				{
					//2:2 4.5 1
					$rateFirst500g = 4.5;
					$rateNext250g = 1;
				}
				elseif($this->settings['shipping']['self_zone'] === 'zone_1')
				{
					//2:1 6.5 1.5
					$rateFirst500g = 6.5;
					$rateNext250g = 1.5;
				}
				elseif($this->settings['shipping']['self_zone'] === 'zone_3')
				{
					//2:3 6 1.5
					$rateFirst500g = 6;
					$rateNext250g = 1.5;
				}
			}
			elseif(in_array($state, $this->settings['shipping']['zone_3']))
			{
				$recipientZone = 'zone_3';
				if($recipientZone === $this->settings['shipping']['self_zone'])
				{
					//3:3 4.5 1
					$rateFirst500g = 4.5;
					$rateNext250g = 1;
				}
				elseif($this->settings['shipping']['self_zone'] === 'zone_1')
				{
					//3:1 7 2
					$rateFirst500g = 7;
					$rateNext250g = 2;
				}
				elseif($this->settings['shipping']['self_zone'] === 'zone_2')
				{
					//3:2 6 1.5
					$rateFirst500g = 6;
					$rateNext250g = 1.5;
				}
			}
			else
			{
				return $response->withStatus(400)->withJson(['message'=>'Sorry we dont serve '.$state.' region']);
			}
		}

		$checkouts = Checkout::select('discount','amount','price','weight')->where('invoice', 'UNKNOWN')->get();

		if (count($checkouts) == 0) {
			return $response->withStatus(400)->withJson(['message'=>'Please add some product to checkout']);
		}

		foreach($checkouts as $index => $checkout)
		{
			$totalAmount += $checkout->amount;
			$totalWeight += $checkout->weight * $checkout->amount;
			$totalPrice += (((100 - $checkout->discount) / 100) * $checkout->price) * $checkout->amount;
		}

		if(!$selfpickup)
		{
			if($totalWeight <= 500)
			{
				$shippingCost = round((($this->settings['shipping']['tax'] / 100) * $rateFirst500g) + $rateFirst500g, 2);
			}
			elseif($totalWeight > 500)
			{
				$overflowWeight = $totalWeight - 500;
				$overflowTimer = $overflowWeight / 250;
				$overflowTimer = ceil($overflowTimer);
				$overflowCost = $overflowTimer * $rateNext250g;
				$shippingCost = round((($this->settings['shipping']['tax'] / 100) * ($rateFirst500g + $overflowCost)) + ($rateFirst500g + $overflowCost), 2);
			}

			$shippingID = Uuid::uuid4()->toString();
			Shipping::create([
				'id' => $shippingID,
				'invoice' => $invoiceID,
				'recipient' => trim($recipient),
				'first_address' => trim($first_address),
				'second_address' => trim($second_address),
				'poscode' => $poscode,
				'city' => trim($city),
				'state' => $state,
				'cost' => $shippingCost,
			]);
		}

		$invoice = Invoice::create([
			'id' => $invoiceID,
			'user' => $uid,
			'shipping' => $shippingID,
			'billing' => $billing,
			'mobile' => $mobile,
			'total_amount' => $totalAmount,
			'total_price' => round(($totalPrice + $shippingCost), 2),
			'total_weight' => $totalWeight,
			'status' => 0,
		]);

		Checkout::where(['invoice' => 'UNKNOWN', 'user' => $uid])->update(['invoice' => $invoiceID]);

		return $response->withStatus(200)->withJson(['message'=>'Successfully generate invoice']);
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
