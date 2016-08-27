<?php

namespace App\Services;

use App\Models\Wishlist as W;

/**
* 
*/
class Wishlist
{
	public function totalWishlist()
	{
		return W::select('id')->where('user','=',isset($_SESSION['user']) ? $_SESSION['user'] : '')->count();
	}
}
