<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Checkout extends Model
{

	protected $table = 'checkouts';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'user',
		'invoice',
		'product',
		'amount',
		'discount',
		'colour',
		'size',
		'price',
		'weight',
		'created_at',
		'updated_at',
	];
}
