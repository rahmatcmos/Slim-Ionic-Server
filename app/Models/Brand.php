<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Brand extends Model
{

	protected $table = 'brands';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'name',
		'slug',
		'created_at',
		'updated_at',
	];
}
