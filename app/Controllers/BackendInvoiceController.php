<?php
namespace App\Controllers;

use App\Models\Checkout;
use App\Models\Invoice;
use App\Models\Shipping;
use App\Models\Product;
use App\Controllers\Controller;
use Illuminate\Pagination;
use Illuminate\Pagination\Paginator;
use Respect\Validation\Validator as v;

class BackendInvoiceController extends Controller
{

	public function getInvoice($request,$response)
	{
		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});

		$invoiceList = Invoice::select('id','total_amount','shipping','total_price','total_weight','status','created_at')->orderBy('created_at', 'desc')->paginate(10);
		$invoiceList->setPath($this->settings['config']['base_url'].'/dashboard/invoice');
		return $this->view->render($response,'invoice/backend/invoice.twig',['invoices'=>$invoiceList,'pageTitle'=>'Manage Invoice']);
	}

	public function searchInvoice($request,$response)
	{
		$currentKeyword = (isset($_GET['keyword']) ? $_GET['keyword'] : false);
		if (!$currentKeyword) {
			return $response->withRedirect($this->router->pathFor('dashboard.manage.invoice'));
		}

		$currentPage = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
		Paginator::currentPageResolver(function () use ($currentPage) {
			return $currentPage;
		});
		$invoiceList = Invoice::select('id','total_amount','shipping','total_price','total_weight','status','created_at')
					->where('id', 'like', '%'.$currentKeyword.'%')
					->orderBy('created_at', 'desc')
					->paginate(10);

		$invoiceList->setPath($this->settings['config']['base_url'].'/dashboard/invoice/search?keyword='.$currentKeyword);
		return $this->view->render($response,'invoice/backend/invoice.twig',['invoices'=>$invoiceList,'pageTitle'=>'Search Invoice']);
	}

	public function viewInvoice($request,$response)
	{
		$cond = ['id' => $request->getParam('id')];
		$invoice = Invoice::select('id','user','billing','mobile','total_amount','total_price','total_weight','status','collector','created_at')->where($cond)->first();
		$data = ['invoice'=>$invoice, 'pageTitle'=>'Invoice - '.$invoice->id, 'shipping_record'=>$invoice->shippingRecord, 'checkout_products'=>$invoice->checkoutProducts];
		return $this->view->render($response,'invoice/backend/view_invoice.twig', $data);
	}

	public function deleteInvoice($request,$response)
	{
		if(Invoice::where(['id' => $request->getParam('id'), 'status' => 0])->delete())
		{
			Shipping::where('invoice', $request->getParam('id'))->delete();

			$cond = ['invoice' => $request->getParam('id')];
			$productCheckouts = Checkout::select('amount','product')->where($cond)->get();

			foreach($productCheckouts as $index => $productData)
			{
				$product = Product::find($productData->product);
				$product->stock = (int) $product->stock + (int) $productData->amount;
				$product->save();
			}

			Checkout::where('invoice', $request->getParam('id'))->delete();
			$this->flash->addMessage('info','Invoice has been deleted');
			return $response->withRedirect($this->router->pathFor('dashboard.manage.invoice'));
		}

		$this->flash->addMessage('error','Operation fail');
		return $response->withRedirect($this->router->pathFor('dashboard.manage.invoice'));
	}

	public function updateInvoice($request,$response)
	{
		//Invoice status
		//Shipping serial
		$validation = $this->validator->validate($request,[
			'id' => v::notEmpty()
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('dashboard.manage.invoice'));
		}

		if($request->getParam('status') == 0) //unpaid
		{
			$validation = $this->validator->validate($request,[
				'serial' => v::not(v::notEmpty())
			]);
			if ($validation->failed()) {
				return $response->withRedirect($this->router->pathFor('dashboard.view.invoice').'?id='.$request->getParam('id'));
			}
			$this->flash->addMessage('info','Invoice status is set to UNPAID::0');
		}
		elseif($request->getParam('status') == 1) //paid
		{
			$this->flash->addMessage('info','Invoice status is set to PAID::1');
			Shipping::where('invoice',$request->getParam('id'))->update(['serial' => 'PROCESSING']);
			Invoice::where('id',$request->getParam('id'))->update(['collector' => 'COLLECTABLE']);
		}
		elseif($request->getParam('status') == 2) //ship out
		{
			if(!$request->getParam('shipping'))
			{
				$this->flash->addMessage('info','SHIP OUT::2 not available for Self-Collect method');
				return $response->withRedirect($this->router->pathFor('dashboard.view.invoice').'?id='.$request->getParam('id'));
			}
			$validation = $this->validator->validate($request,[
				'serial' => v::notEmpty()
			]);
			if ($validation->failed()) {
				return $response->withRedirect($this->router->pathFor('dashboard.view.invoice').'?id='.$request->getParam('id'));
			}
			$this->flash->addMessage('info','Invoice status is set to SHIP OUT::2');
			Shipping::where('invoice',$request->getParam('id'))->update(['serial' => $request->getParam('serial')]);
		}
		elseif($request->getParam('status') == 3) //collected
		{
			$validation = $this->validator->validate($request,[
				'serial' => v::notEmpty()
			]);
			if ($validation->failed()) {
				return $response->withRedirect($this->router->pathFor('dashboard.view.invoice').'?id='.$request->getParam('id'));
			}
			$this->flash->addMessage('info','Invoice status is set to COLLECTED::3');
			Invoice::where('id',$request->getParam('id'))
				->update(['status' => $request->getParam('status'), 'collector' => $request->getParam('serial')]);
		}
		elseif($request->getParam('status') == 4) //returned
		{
			if($request->getParam('restore'))
			{
				$cond = ['invoice' => $request->getParam('id')];
				$productCheckouts = Checkout::select('amount','product')->where($cond)->get();

				foreach($productCheckouts as $index => $productData)
				{
					$product = Product::find($productData->product);
					$product->stock = (int) $product->stock + (int) $productData->amount;
					$product->save();
				}
				$this->flash->addMessage('info','Invoice status is set to RETURNED::4 and product stock has been restored');
			}
			else
			{
				$this->flash->addMessage('info','Invoice status is set to RETURNED::4');
			}

			Invoice::where('id',$request->getParam('id'))->update(['status' => $request->getParam('status')]);
		}

		Invoice::where('id',$request->getParam('id'))->update(['status' => $request->getParam('status')]);
		return $response->withRedirect($this->router->pathFor('dashboard.view.invoice').'?id='.$request->getParam('id'));
	}
}
