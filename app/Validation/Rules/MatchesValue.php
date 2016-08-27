<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class MatchesValue extends AbstractRule
{
	protected $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function validate($input)
	{
		return ((String) $input === (String) $this->value);
	}
}
