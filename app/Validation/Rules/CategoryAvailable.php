<?php

namespace App\Validation\Rules;

use App\Models\Category;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class CategoryAvailable extends AbstractRule
{
	public function validate($input)
	{
		return Category::where('name',$input)->count() === 0; 
	}
}
