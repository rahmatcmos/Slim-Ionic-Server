<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Category extends Model
{

	protected $table = 'categories';

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
