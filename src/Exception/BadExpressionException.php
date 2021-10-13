<?php

namespace App\Exception;

use Throwable;

class BadExpressionException extends ConsoleException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Expression Error: " . $message;

        parent::__construct($message, $code, $previous);
    }

}
