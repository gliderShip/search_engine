<?php

namespace App\Model;

interface ExpressionInterface
{
    public function __construct(TokenInterface $token, int $startStartIndex, int $endIndex);

    public function getToken(): TokenInterface;

    public function setToken(TokenInterface $token): void;

    public function getLeftExpression(): ?ExpressionInterface;

    public function getRightExpression(): ?ExpressionInterface;

    public function getEndIndex(): int;

    public function setStartIndex(int $startIndex);

    public function setEndIndex(int $endIndex);



}
