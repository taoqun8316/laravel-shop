<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    use ApiResponse;

    public function __construct(string $message = "", int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(Request $request)
    {
        return $this->error($this->message, $this->code);
    }
}
