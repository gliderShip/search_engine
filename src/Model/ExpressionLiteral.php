<?php

namespace App\Model;

use http\Exception\BadMethodCallException;

class ExpressionLiteral implements ExpressionInterface
{
    /**
     * @var TokenLiteral $literal
     */
    private TokenLiteral $literal;

    /**
     * @param TokenLiteral $token
     */
    public function __construct(TokenInterface $token)
    {
        if (!($token instanceof TokenLiteral)) {
            throw new BadMethodCallException("Expression Literal requires a TokenLiteral. Provided ->:" . get_class($token));
        }

        $this->literal = $token;
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
}
