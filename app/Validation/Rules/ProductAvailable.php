<?php

namespace App\Validation\Rules;

use App\Models\Product;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class ProductAvailable extends AbstractRule
{
	public function validate($input)
	{
		return Product::where('name',$input)->count() === 0; 
	}
}
