<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Shipping extends Model
{

	protected $table = 'shippings';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'invoice',
		'recipient',
		'first_address',
		'second_address',
		'poscode',
		'city',
		'state',
		'cost',
		'serial',
		'created_at',
		'updated_at',
	];
}
