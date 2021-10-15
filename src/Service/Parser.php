<?php

namespace App\Service;

use App\Model\Expression;
use App\Model\ExpressionInterface;
use App\Model\ExpressionLiteral;
use App\Model\TokenInterface;
use App\Model\TokenLiteral;
use App\Model\TokenOperator;
use http\Exception\BadMethodCallException;
use Psr\Log\LoggerInterface;

class Parser
{
    private DocumentManager $documentManager;

    private ExpressionManager $expressionService;

    private LoggerInterface $logger;


    public function __construct(DocumentManager $documentManager, ExpressionManager $expressionService, LoggerInterface $logger)
    {
        $this->documentManager = $documentManager;
        $this->logger = $logger;
        $this->expressionService = $expressionService;
    }

    /**
     * @throws \App\Exception\BadExpressionException
     */
    public function parse($command)
    {
        $expression = $this->expressionService->tokenize($command);
        $this->logger->debug("Parsing Command ->:".$command);

        if ($expression) {
            $value = $this->evaluateExpression($expression);
            $this->logger->debug("Value ->:".$value);
        }
    }

    private function evaluateExpression(ExpressionInterface $expression)
    {
        if($expression instanceof ExpressionLiteral){
            return $this->evaluateLiteral($expression);
        }

        $leftExpression = $this->evaluateExpression($expression->getLeftExpression());
        $rightExpression = $this->evaluateExpression($expression->getRightExpression());
        if($leftExpression instanceof ExpressionLiteral and $rightExpression instanceof ExpressionLiteral){
            return $this->evaluateSimpleExpression($expression);
        }




    }

    private function evaluateLiteral(ExpressionLiteral $literal)
    {
        return $literal->getToken()->getLexeme();
    }

    private function evaluateSimpleExpression(ExpressionInterface $expression)
    {
        $leftExpression = $expression->getLeftExpression();
        if($leftExpression instanceof ExpressionLiteral){
            throw new BadMethodCallException($leftExpression->getToken()->getLexeme(). 'is not a literal');
        }

        $rightExpression = $expression->getRightExpression();
        if($rightExpression instanceof ExpressionLiteral){
            throw new BadMethodCallException($rightExpression->getToken()->getLexeme(). 'is not a literal');
        }

        $operator = $expression->getToken();

        $this->logger->debug($leftExpression->getToken()->getLexeme()." -- ".$operator->getLexeme()." -- ".$rightExpression->getToken()->getLexeme());
    }


}
