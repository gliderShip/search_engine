<?php

namespace App\Model;

use http\Exception\BadMethodCallException;

class TokenSpace extends Token implements TokenInterface
{
        public const TYPE = 'SPACE';

    public const SPACE_LEXEME = ' ';//space

    public const EXPRESSION_DELIMITERS = [
        self::TYPE => self::SPACE_LEXEME
    ];

    public function __construct(string $lexeme, int $position)
    {
        parent::__construct($lexeme, $position);
    }

    public static function isValidLexeme(string $lexeme): bool
    {
        if (in_array($lexeme, self::EXPRESSION_DELIMITERS)) {
            return true;
        }

        return false;
    }
}
