<?php

namespace App\Model;

use http\Exception\BadMethodCallException;
use http\Exception\InvalidArgumentException;

class Token
{
    public const AND_OPERATOR = '&';
    public const OR_OPERATOR = '|';

    public const LEFT_BRACKET_LEXEME = '(';
    public const RIGHT_BRACKET_LEXEME = ')';

    public const TYPE_LITERAL = 'LITERAL';
    public const TYPE_OPERATOR = 'OPERATOR';
    public const TYPE_SPACE = 'SPACE';
    public const TYPE_BRACKET = 'BRACKET';

    public const EXPRESSION_DELIMITERS = [
        self::TYPE_SPACE
    ];

    public const BRACKETS = [self::LEFT_BRACKET_LEXEME, self::RIGHT_BRACKET_LEXEME];
    public const OPERATORS = [self::AND_OPERATOR, self::OR_OPERATOR];

    public const TYPES = [self::TYPE_LITERAL, self::TYPE_OPERATOR, self::TYPE_SPACE, self::TYPE_BRACKET];

    private string $lexeme;

    private string $type;

    /**
     * @var int starting position in source
     */
    private int $position;

    /**
     * @param string $lexeme
     * @param string $type
     * @param int $position
     */
    public function __construct(string $lexeme, int $position, string $type = null)
    {
        $this->lexeme = $lexeme;

        if ($position < 0) {
            throw new BadMethodCallException("Invalid lexeme position ->:$position");
        } else {
            $this->position = $position;
        }

        if ($type) {
            self::validateLexemeType($lexeme, $type);
            $this->type = $type;
        } else {
            $this->type = self::guessLexemeType($lexeme);
        }
    }

    public static function validateLexemeType(string $lexeme, string $type)
    {
        switch ($type) {
            case self::TYPE_LITERAL:
                if (!self::isLexemeLiteral($lexeme)) {
                    throw new BadMethodCallException("Invalid literal lexeme ->:$lexeme");
                }
                break;
            case self::TYPE_BRACKET:
                if (!self::isLexemeBracket($lexeme)) {
                    throw new BadMethodCallException("Invalid bracket lexeme ->:$lexeme");
                }
                break;
            case self::TYPE_SPACE:
                if (!self::isLexemeSpace($lexeme)) {
                    throw new BadMethodCallException("Invalid space lexeme ->:$lexeme");
                }
                break;
            case self::TYPE_OPERATOR:
                if (!self::isLexemeOperator($lexeme)) {
                    throw new BadMethodCallException("Invalid operator lexeme ->:$lexeme");
                }
                break;
            default:
                throw new BadMethodCallException("Invalid lexeme type->:$type");
        }
    }

    public static function isLexemeLiteral(string $lexeme): bool
    {
        if (ctype_alnum($lexeme)) {
            return true;
        }

        return false;
    }

    public static function isLexemeBracket(string $lexeme): bool
    {
        if (in_array($lexeme, self::BRACKETS)) {
            return true;
        }

        return false;
    }

    public static function isLexemeSpace(string $lexeme): bool
    {
        if (in_array($lexeme, self::EXPRESSION_DELIMITERS)) {
            return true;
        }

        return false;
    }

    public static function isLexemeOperator(string $lexeme): bool
    {
        if (in_array($lexeme, self::OPERATORS)) {
            return true;
        }

        return false;
    }

    private static function guessLexemeType(string $lexeme): string
    {
        if (in_array($lexeme, self::EXPRESSION_DELIMITERS)) {
            return self::TYPE_SPACE;
        } elseif (in_array($lexeme, self::BRACKETS)) {
            return self::TYPE_BRACKET;
        } elseif (in_array($lexeme, self::OPERATORS)) {
            return self::TYPE_OPERATOR;
        } elseif (ctype_alnum($lexeme)) {
            return self::TYPE_LITERAL;
        } else {
            throw new InvalidArgumentException("Invalid lexeme ->:" . $lexeme);
        }
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    public function getEndPosition(): int
    {
        return $this->position + strlen($this->lexeme) - 1;
    }

    public function isLiteral(): bool
    {
        return $this->getType() == self::TYPE_LITERAL;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    public function isOperator(): bool
    {
        return $this->getType() == self::TYPE_OPERATOR;
    }

    public function isSpace(): bool
    {
        return $this->getType() == self::TYPE_SPACE;
    }

    public function isBracket(): bool
    {
        return $this->getType() == self::TYPE_BRACKET;
    }

    public function isOpenBracket(): bool
    {
        return ($this->getType() == self::TYPE_BRACKET) && ($this->getLexeme() == self::LEFT_BRACKET_LEXEME);
    }

    /**
     * @return mixed
     */
    public function getLexeme()
    {
        return $this->lexeme;
    }

    public function isClosedBracket(): bool
    {
        return ($this->getType() == self::TYPE_BRACKET) && ($this->getLexeme() == self::RIGHT_BRACKET_LEXEME);
    }


}
