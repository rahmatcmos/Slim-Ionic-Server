<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Invoice extends Model
{

	protected $table = 'invoices';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'user',
		'shipping',
		'billing',
		'mobile',
		'total_amount',
		'total_price',
		'total_weight',
		'status',
		'collector',
		'created_at',
		'updated_at',
	];

	public function checkoutProducts()
	{
		return $this->hasMany('App\Models\Checkout', 'invoice');
	}

	public function shippingRecord()
	{
		return $this->hasOne('App\Models\Shipping', 'invoice');
	}
}
