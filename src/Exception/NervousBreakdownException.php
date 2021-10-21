<?php

namespace App\Exception;

class NervousBreakdownException extends \Exception implements ConsoleExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "error: " . $message;

        parent::__construct($message, $code, $previous);
    }
}
