<?php

namespace App\Exception;

use Throwable;

class ConsoleException extends \Exception implements ConsoleExceptionInterface
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
