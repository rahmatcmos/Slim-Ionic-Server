<?php
namespace App\Controllers;

use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\Shipping;
use App\Models\Product;
use App\Controllers\Controller;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Ramsey\Uuid\Uuid;
use Respect\Validation\Validator as v;

class FrontendInvoiceController extends Controller
{

	public function getInvoice($request,$response)
	{
		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$invoiceList = Invoice::select('id','total_amount','shipping','total_price','total_weight','status','created_at')->where('user',$_SESSION['user'])
					->orderBy('created_at', 'desc')
					->paginate(10);
		$invoiceList->setPath($this->settings['config']['base_url'].'/invoice');
		return $this->view->render($response,'invoice/frontend/invoice.twig',['invoices'=>$invoiceList]);
	}

	public function viewInvoice($request,$response)
	{
		$cond = ['id' => $request->getParam('id'), 'user' => $_SESSION['user']];
		$invoice = Invoice::select('id','user','billing','mobile','total_amount','total_price','total_weight','status','collector','created_at')->where($cond)->first();

		if(!$invoice) 
		{
			$this->flash->addMessage('error','Operation fail');
			return $response->withRedirect($this->router->pathFor('frontend.invoice.index'));
		}

		$data = ['invoice'=>$invoice, 'shipping_record'=>$invoice->shippingRecord, 'checkout_products'=>$invoice->checkoutProducts];
		return $this->view->render($response,'invoice/frontend/view_invoice.twig', $data);
	}

	public function deleteInvoice($request,$response)
	{
		if(Invoice::where(['id' => $request->getParam('id'), 'user' => $_SESSION['user'], 'status' => 0])->delete())
		{
			Shipping::where('invoice', $request->getParam('id'))->delete();

			$cond = ['invoice' => $request->getParam('id'), 'user' => $_SESSION['user']];
			$productCheckouts = Checkout::select('amount','product')->where($cond)->get();

			foreach($productCheckouts as $index => $productData)
			{
				$product = Product::find($productData->product);
				$product->stock = (int) $product->stock + (int) $productData->amount;
				$product->save();
			}

			Checkout::where('invoice', $request->getParam('id'))->delete();
			$this->flash->addMessage('info','Invoice has been deleted');
			return $response->withRedirect($this->router->pathFor('frontend.invoice.index'));
		}

		$this->flash->addMessage('error','Operation fail');
		return $response->withRedirect($this->router->pathFor('frontend.invoice.index'));
	}

	public function generateInvoice($request,$response)
	{
		$totalAmount = 0;
		$totalPrice = 0;
		$totalWeight = 0;
		$rateFirst500g = 0;
		$rateNext250g = 0;
		$shippingCost = 0;
		$shippingID = 'selfpickup';
		$invoiceID = Uuid::uuid4()->toString();

		if($request->getParam('selfpickup'))
		{
			$validation = $this->validator->validate($request,[
				'billing' => v::notEmpty(),
				'mobile' => v::notEmpty()->phone()->length(10,13),
			]);

			if ($validation->failed()) {
				return $response->withRedirect($this->router->pathFor('checkout.index'));
			}
		}
		else
		{
			$validation = $this->validator->validate($request,[
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
				return $response->withRedirect($this->router->pathFor('checkout.index'));
			}

			if((int) $request->getParam('poscode') === $this->settings['shipping']['self_poscode'])
			{
				//local 4 0.8
				$rateFirst500g = 4;
				$rateNext250g = 0.8;
			}
			elseif(in_array($request->getParam('state'), $this->settings['shipping']['zone_1']))
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
			elseif(in_array($request->getParam('state'), $this->settings['shipping']['zone_2']))
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
			elseif(in_array($request->getParam('state'), $this->settings['shipping']['zone_3']))
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
				$this->flash->addMessage('error','Sorry we dont serve '.$state.' region');
				return $response->withRedirect($this->router->pathFor('checkout.index'));
			}
		}

		$checkouts = Checkout::select('discount','amount','price','weight')->where('invoice', 'UNKNOWN')->get();

		if (count($checkouts) == 0) {
			$this->flash->addMessage('error','Please add some product to checkout');
			return $response->withRedirect($this->router->pathFor('checkout.index'));
		}

		foreach($checkouts as $index => $checkout)
		{
			$totalAmount += $checkout->amount;
			$totalWeight += $checkout->weight * $checkout->amount;
			$totalPrice += (((100 - $checkout->discount) / 100) * $checkout->price) * $checkout->amount;
		}

		if(!$request->getParam('selfpickup'))
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
				'recipient' => trim($request->getParam('recipient')),
				'first_address' => trim($request->getParam('first_address')),
				'second_address' => trim($request->getParam('second_address')),
				'poscode' => $request->getParam('poscode'),
				'city' => trim($request->getParam('city')),
				'state' => $request->getParam('state'),
				'cost' => $shippingCost,
			]);
		}

		$invoice = Invoice::create([
			'id' => $invoiceID,
			'user' => $_SESSION['user'],
			'shipping' => $shippingID,
			'billing' => $request->getParam('billing'),
			'mobile' => $request->getParam('mobile'),
			'total_amount' => $totalAmount,
			'total_price' => $totalPrice + $shippingCost,
			'total_weight' => $totalWeight,
			'status' => 0,
		]);

		Checkout::where(['invoice' => 'UNKNOWN', 'user' => $_SESSION['user']])->update(['invoice' => $invoiceID]);

		$this->flash->addMessage('info','Successfully generate invoice');
		return $response->withRedirect($this->router->pathFor('frontend.invoice.index'));
	}
}
