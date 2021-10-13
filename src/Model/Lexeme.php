<?php

namespace App\Model;

class Lexeme
{
//    public const LEFT_BRACKET_OPERATOR = '(';
//    public const RIGHT_BRACKET_OPERATOR = ')';
//    public const LEXEME_DELIMITERS = [
//        ' '//space
//    ];

    private string $lexeme;
    private int $startPosition;

    public function __construct(string $lexeme, int $startPosition)
    {
        $this->lexeme = $lexeme;
        $this->startPosition = $startPosition;
    }

    private function getEndPosition(): int
    {
        return $this->startPosition + $this->getLength() - 1;
    }

    private function getLength(): int
    {
        return strlen($this->lexeme);
    }


}
