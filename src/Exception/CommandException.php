<?php

namespace App\Exception;

use Throwable;

class CommandException extends ConsoleException
{
    public function __construct(string $command = "", string $message = "", int $code = 0, Throwable $previous = null)
    {
        $message = "$command error : $message";

        parent::__construct($message, $code, $previous);
    }

}
