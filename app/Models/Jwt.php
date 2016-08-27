<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class Jwt extends Model
{

	protected $table = 'jwts';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'user',
		'ip_address',
		'audience',
		'os',
		'expired_at',
		'created_at',
		'updated_at',
	];
}
