<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class BrandAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Brand name is not available',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Brand name is available',
        ]
    ];
}
