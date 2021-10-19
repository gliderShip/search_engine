<?php

namespace App\Model;

use http\Exception\BadMethodCallException;

abstract class Token
{
    public const TYPE = 'ABSTRACT';

    /**
     * @var string
     */
    protected string $lexeme;

    /**
     * @var int starting position in source
     */
    protected int $position;

    /**
     * @param string $lexeme
     * @param string $type
     * @param int $position
     */
    public function __construct(string $lexeme, int $position)
    {
        if (!static::isValidLexeme($lexeme)) {
            throw new BadMethodCallException("Invalid lexeme ->:$lexeme for type ->:" . get_called_class());
        }

        $this->lexeme = $lexeme;

        if ($position < 0) {
            throw new BadMethodCallException("Invalid lexeme position ->:$position");
        } else {
            $this->position = $position;
        }
    }

    public static function getType(): string
    {
        return static::TYPE;
    }

    public abstract static function isValidLexeme(string $lexeme): bool;

    /**
     * @return int
     */
    public function getStartPosition(): int
    {
        return $this->position;
    }

    public function getEndPosition(): int
    {
        return $this->position + strlen($this->lexeme) - 1;
    }

    /**
     * @return string
     */
    public function getLexeme(): string
    {
        return $this->lexeme;
    }


}
