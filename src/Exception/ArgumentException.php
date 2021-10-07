<?php

namespace App\Exception;

use Throwable;

class ArgumentException extends ConsoleException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Argument Error: " . $message;

        parent::__construct($message, $code, $previous);
    }

}
