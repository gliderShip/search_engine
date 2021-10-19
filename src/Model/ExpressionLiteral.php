<?php

namespace App\Model;

use http\Exception\BadMethodCallException;

class ExpressionLiteral implements ExpressionInterface
{

    private int $startIndex = 0;
    private int $endIndex;

    private TokenLiteral $literal;


    /**
     * @param TokenLiteral $token
     */
    public function __construct(TokenInterface $token, int $startIndex = 0, int $endIndex = null)
    {
        if (!($token instanceof TokenLiteral)) {
            throw new BadMethodCallException("Expression Literal requires a TokenLiteral. Provided ->:" . get_class($token));
        }

        $this->literal = $token;
        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;

    }

    /**
     * @return TokenLiteral
     */
    public function getToken(): TokenLiteral
    {
        return $this->literal;
    }

    /**
     * @param TokenLiteral $literal
     */
    public function setToken(TokenInterface $token): void
    {
        if (!($token instanceof TokenLiteral)) {
            throw new BadMethodCallException("Expression Literal requires a TokenLiteral. Provided ->:" . get_class($token));
        }

        $this->literal = $token;
    }

    public function getLeftExpression(): ?ExpressionInterface
    {
        return null;
    }

    public function getRightExpression(): ?ExpressionInterface
    {
        return null;
    }

    public function getEndIndex(): int
    {
        return $this->endIndex;
    }

    public function setStartIndex(int $startIndex)
    {
        $this->startIndex = $startIndex;
    }

    public function setEndIndex(int $endIndex)
    {
        $this->endIndex = $endIndex;
    }


}
