<?php

namespace App\Exceptions;

use Throwable;
use \Exception;

class ApiException extends Exception
{
    function __construct(string $message = "", int $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render()
    {
        return response()->json([
            'status' => false,
            'message' => $this->message,
            'code' => $this->code
        ], $this->code);
    }
}