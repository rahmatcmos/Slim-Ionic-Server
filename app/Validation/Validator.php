<?php 

namespace App\Validation;

use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException;

/**
* 
*/
class Validator
{
	protected $errors;

	public function validate($request,array $rules)
	{
		foreach ($rules as $field => $rule) {
			try {
				$rule->setName(ucfirst(str_replace('_', ' ', $field)))->assert($request->getParam($field));
			} catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
			}
		}

		$_SESSION['errors'] = $this->errors;
		return $this;
	}

	public function validateApi($postArray,array $rules)
	{
		foreach ($rules as $field => $rule) {
			try {
				$rule->setName(ucfirst(str_replace('_', ' ', $field)))->assert($postArray[$field]);
			} catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
			}
		}

		return $this;
	}

	public function failed()
	{
		return !empty($this->errors);
	}

	public function getError()
	{
		return $this->errors;
	}
}
