<?php

namespace App\Service;

use App\Model\ExpressionInterface;
use App\Model\ExpressionLiteral;

class Optimizer
{

    /**
     * @param ExpressionInterface $expression
     * @return ExpressionInterface
     * Optimizes A op A => A, (A op B) op (B op A) => A op B , recursively
     */
    public function optimize(ExpressionInterface $expression): ExpressionInterface
    {
        if ($expression instanceof ExpressionLiteral) {
            return $expression;
        }

        $leftExpression = $expression->getLeftExpression();
        if ($leftExpression) {
            $leftExpression = $this->optimize($leftExpression);
        }

        $operator = $expression->getToken();
        $rightExpression = $expression->getRightExpression();
        if ($rightExpression) {
            $rightExpression = $this->optimize($rightExpression);
        }

        if ($this->equals($leftExpression, $rightExpression)) {
            return $leftExpression;
        }

        return $expression;
    }

    public function equals(ExpressionInterface $left, ExpressionInterface $right): bool
    {
        if (get_class($left) == get_class($right)) {
            $leftFirstChild = $left->getLeftExpression();
            $leftOperator = $left->getToken()->getLexeme();
            $leftSecondChild = $left->getRightExpression();

            $rightFirstChild = $right->getLeftExpression();
            $rightOperator = $right->getToken()->getLexeme();
            $rightSecondChild = $left->getRightExpression();


            if (($leftOperator == $rightOperator) && $this->equals($leftFirstChild, $rightFirstChild) && $this->equals($leftSecondChild, $rightSecondChild)) {
                return true;
            }
            if (($leftOperator == $rightOperator) && $this->equals($leftFirstChild, $rightSecondChild) && $this->equals($leftSecondChild, $rightFirstChild)) {
                return true;
            }

        }

        return false;
    }
}
