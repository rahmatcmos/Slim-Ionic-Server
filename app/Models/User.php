<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* 
*/
class User extends Model
{

	protected $table = 'users';

	protected $primaryKey = 'id';

	public $incrementing = false;

	protected $fillable = [
		'id',
		'name',
		'email',
		'password',
		'role',
		'status',
	];

	public function setPassword($password)
	{
		$this->update([
			'password' => password_hash($password,PASSWORD_DEFAULT)
		]);
	}
}
