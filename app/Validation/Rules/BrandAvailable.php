<?php

namespace App\Validation\Rules;

use App\Models\Brand;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class BrandAvailable extends AbstractRule
{
	public function validate($input)
	{
		return Brand::where('name',$input)->count() === 0; 
	}
}
