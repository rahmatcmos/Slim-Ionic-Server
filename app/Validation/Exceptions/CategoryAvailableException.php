<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class CategoryAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Category name is not available',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Category name is available',
        ]
    ];
}
