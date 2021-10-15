<?php

namespace App\Model;

class CompoundExpression implements ExpressionInterface
{
    /**
     * @var TokenOperator $operator
     */
    private TokenOperator $operator;

    private ExpressionInterface $leftExpression;

    private ExpressionInterface $rightExpression;

    /**
     * @param Token $token
     */
    public function __construct(TokenInterface $token)
    {
        if (!($token instanceof TokenOperator)) {
            throw new \BadMethodCallException("CompoundExpression requires a TokenOperator. Provided ->:" . get_class($token));
        }

        $this->operator = $token;
    }

    /**
     * @return TokenOperator
     */
    public function getToken(): TokenOperator
    {
        return $this->operator;
    }

    /**
     * @param TokenInterface $token
     */
    public function setToken(TokenInterface $token): void
    {
        if (!($token instanceof TokenOperator)) {
            throw new \BadMethodCallException("CompoundExpression requires a TokenOperator. Provided ->:" . get_class($token));
        }

        $this->operator = $token;
    }

    /**
     * @return ExpressionInterface
     */
    public function getLeftExpression(): ?ExpressionInterface
    {
        return $this->leftExpression;
    }

    /**
     * @param ExpressionInterface $leftExpression
     */
    public function setLeftExpression(ExpressionInterface $leftExpression): void
    {
        $this->leftExpression = $leftExpression;
    }

    /**
     * @return ExpressionInterface
     */
    public function getRightExpression(): ?ExpressionInterface
    {
        return $this->rightExpression;
    }

    /**
     * @param ExpressionInterface $rightExpression
     */
    public function setRightExpression(ExpressionInterface $rightExpression): void
    {
        $this->rightExpression = $rightExpression;
    }
}
