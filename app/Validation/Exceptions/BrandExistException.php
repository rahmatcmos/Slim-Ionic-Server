<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class BrandExistException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Brand name is not exist',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Brand name is exist',
        ]
    ];
}
