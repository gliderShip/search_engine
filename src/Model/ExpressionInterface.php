<?php

namespace App\Model;

interface ExpressionInterface
{
    public function __construct(TokenInterface $token);

    public function getToken(): TokenInterface;

    public function setToken(TokenInterface $token): void;

    public function getLeftExpression(): ?ExpressionInterface;

    public function getRightExpression(): ?ExpressionInterface;
}
