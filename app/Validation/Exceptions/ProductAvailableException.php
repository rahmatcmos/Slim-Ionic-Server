<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class ProductAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Product name is not available',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Product name is available',
        ]
    ];
}
