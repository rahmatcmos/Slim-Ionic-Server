<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Wishlist extends Model
{

	protected $table = 'wishlists';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'user',
		'product',
		'created_at',
		'updated_at',
	];
}
