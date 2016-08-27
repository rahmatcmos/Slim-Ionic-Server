<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Product extends Model
{

	protected $table = 'products';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'name',
		'slug',
		'detail',
		'stock',
		'price',
		'discount',
		'colour',
		'size',
		'weight',
		'category',
		'brand',
		'photo_1',
		'photo_2',
		'photo_3',
		'created_at',
		'updated_at',
	];
}
