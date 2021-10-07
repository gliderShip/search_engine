<?php

namespace App\Exception;

use Throwable;

class ConsoleException extends \Exception implements ConsoleExceptionInterface
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "index error " . $message;

        parent::__construct($message, $code, $previous);
    }

}
