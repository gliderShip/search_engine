<?php

namespace App\Model;

class TokenOperator extends Token implements TokenInterface
{
    public const TYPE = 'OPERATOR';

    public const AND_OPERATOR = ['NAME' => 'AND', 'LEXEME' => '&'];
    public const OR_OPERATOR = ['NAME' => 'OR', 'LEXEME' => '|'];

    public const OPERATORS = [
        self::AND_OPERATOR['LEXEME'],
        self::OR_OPERATOR['LEXEME']
    ];

    public function __construct(string $lexeme, int $position)
    {
        parent::__construct($lexeme, $position);
    }

    public static function isValidLexeme(string $lexeme): bool
    {
        if (in_array($lexeme, self::OPERATORS)) {
            return true;
        }

        return false;
    }

}
