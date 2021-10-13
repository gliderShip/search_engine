<?php

namespace App\Model;

class Literal
{
    private Lexeme $lexeme;

    /**
     * @param Lexeme $lexeme
     */
    public function __construct(Lexeme $lexeme)
    {
        if (!ctype_alnum($lexeme)) {
            throw new \BadMethodCallException("Invalid lexeme ->:" . $lexeme);
        }

        $this->lexeme = $lexeme;
    }


}
