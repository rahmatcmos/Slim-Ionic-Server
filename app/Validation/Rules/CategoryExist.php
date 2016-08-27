<?php

namespace App\Validation\Rules;

use App\Models\Category;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class CategoryExist extends AbstractRule
{
	public function validate($input)
	{
		return Category::where('id',$input)->count() === 1; 
	}
}
