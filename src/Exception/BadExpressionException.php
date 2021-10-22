<?php

namespace App\Exception;

use Throwable;

class BadExpressionException extends ConsoleException
{
    public function __construct($command = '', $message = "", $code = 0, Throwable $previous = null)
    {
        $message = "$command error Expression: $message";

        parent::__construct($message, $code, $previous);
    }

}
