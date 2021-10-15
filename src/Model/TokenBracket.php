<?php

namespace App\Model;

class TokenBracket extends Token implements TokenInterface
{
    public const TYPE = 'BRACKET';

    public const LEFT_BRACKET = ['NAME' => 'OPENING_BRACKET', 'LEXEME' => '('];
    public const RIGHT_BRACKET = ['NAME' => 'CLOSING_BRACKET', 'LEXEME' => ')'];

    public const BRACKETS = [
        self::LEFT_BRACKET['LEXEME'],
        self::RIGHT_BRACKET['LEXEME']
    ];

    public function __construct(string $lexeme, int $position)
    {
        parent::__construct($lexeme, $position);
    }

    public static function isValidLexeme(string $lexeme): bool
    {
        if (in_array($lexeme, self::BRACKETS)) {
            return true;
        }

        return false;
    }
}
