<?php

namespace App\Exception;

use Throwable;

class ArgumentException extends ConsoleException
{
    public function __construct(string $command = '', $message = "", $code = 0, Throwable $previous = null)
    {
        $message = "$command error : $message";

        parent::__construct($message, $code, $previous);
    }

}
