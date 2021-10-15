<?php

namespace App\Model;

class TokenLiteral extends Token implements TokenInterface
{
    public const TYPE = 'LITERAL';

    public function __construct(string $lexeme, int $position)
    {
        parent::__construct($lexeme, $position);
    }

    public static function isValidLexeme(string $lexeme): bool
    {
        if (ctype_alnum($lexeme)) {
            return true;
        }

        return false;
    }

}
