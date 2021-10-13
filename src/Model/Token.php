<?php

namespace App\Model;

class Token
{
    public const TERM = 'TERM';
    public const AND_OPERATOR = '&';
    public const OR_OPERATOR = '|';
    public const LEFT_BRACKET_OPERATOR = '(';
    public const RIGHT_BRACKET_OPERATOR = ')';
    public const EXPRESSION_DELIMITERS = [
        ' '//space
    ];

    public const OPERATORS = [
        self::AND_OPERATOR,
        self::OR_OPERATOR,
        self::LEFT_BRACKET_OPERATOR,
        self::RIGHT_BRACKET_OPERATOR
    ];

    public const LEXEMES = self::EXPRESSION_DELIMITERS + self::OPERATORS;

    public const TYPES = [self::TERM];

    private string $lexeme;

    private string $type;

    /**
     * @var int starting position in source
     */
    private int $position;

    /**
     * @param $lexeme
     * @param $type
     */
    public function __construct(string $lexeme, string $type, int $position)
    {
        $this->lexeme = $lexeme;
        $this->type = $type;
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getLexeme()
    {
        return $this->lexeme;
    }

    /**
     * @param mixed $lexeme
     */
    public function setLexeme($lexeme): void
    {
        $this->lexeme = $lexeme;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getEndPosition(): int
    {
        return $this->position + strlen($this->lexeme) -1;
    }


}
