<?php

namespace App\Model;

class CompoundExpression implements ExpressionInterface
{
    private int $startIndex;

    private int $endIndex;

    private TokenOperator $operator;

    private ExpressionInterface $leftExpression;

    private ExpressionInterface $rightExpression;


    /**
     * @param Token $token
     */
    public function __construct(TokenInterface $token, int $startIndex = 0, int $endIndex = null)
    {
        if (!($token instanceof TokenOperator)) {
            throw new \BadMethodCallException("CompoundExpression requires a TokenOperator. Provided ->:" . get_class($token));
        }

        $this->operator = $token;
        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;

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
