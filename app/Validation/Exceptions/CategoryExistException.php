<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class CategoryExistException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => 'Category name is not exist',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => 'Category name is exist',
        ]
    ];
}
