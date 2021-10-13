<?php

namespace App\Model;

class Expression
{
    /**
     * @var Token $token
     */
    private Token $token;

    private ?Expression $leftExpression = null;

    private ?Expression $rightExpression = null;

    /**
     * @param Token $token
     */
    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @return Token
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token): void
    {
        $this->token = $token;
    }

    /**
     * @return Expression|null
     */
    public function getLeftExpression(): ?Expression
    {
        return $this->leftExpression;
    }

    /**
     * @param Expression|null $leftExpression
     */
    public function setLeftExpression(?Expression $leftExpression): void
    {
        $this->leftExpression = $leftExpression;
    }

    /**
     * @return Expression|null
     */
    public function getRightExpression(): ?Expression
    {
        return $this->rightExpression;
    }

    /**
     * @param Expression|null $rightExpression
     */
    public function setRightExpression(?Expression $rightExpression): void
    {
        $this->rightExpression = $rightExpression;
    }


}
