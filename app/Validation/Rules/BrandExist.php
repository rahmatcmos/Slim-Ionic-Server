<?php

namespace App\Validation\Rules;

use App\Models\Brand;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class BrandExist extends AbstractRule
{
	public function validate($input)
	{
		return Brand::where('id',$input)->count() === 1; 
	}
}
