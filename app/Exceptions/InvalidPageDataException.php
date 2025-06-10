<?php

namespace App\Exceptions;

use Exception;

class InvalidPageDataException extends Exception
{
    public function __construct($message = "Os dados da página são inválidos ou estão ausentes.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
