<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class ExistEmailException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} is not exist',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} is exist',
        ]
    ];
}
