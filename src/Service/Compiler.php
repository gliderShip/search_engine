<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\Expression;
use App\Model\ExpressionInterface;
use App\Model\ExpressionLiteral;
use App\Model\TokenOperator;
use Psr\Log\LoggerInterface;

class Compiler
{
    private DocumentManager $documentManager;

    private Parser $parser;

    private LoggerInterface $logger;

    public function __construct(DocumentManager $documentManager, Parser $parser, LoggerInterface $logger)
    {
        $this->documentManager = $documentManager;
        $this->logger = $logger;
        $this->parser = $parser;
    }

    /**
     * @throws BadExpressionException
     */
    public function execute($command)
    {
        $this->logger->debug("Parsing Command ->:" . $command);

        $syntaxTree = $this->parser->tokenize($command);
        $this->logger->debug("Syntax Tree ->:" . print_r($syntaxTree));

        if ($syntaxTree) {
            $resultId = $this->evaluateExpression($syntaxTree);
            $result = $this->documentManager->getStorageManager()->getEntityById($resultId);
            $this->logger->debug(__METHOD__.'  Result', [$result]);
        }
    }

    private function evaluateExpression(ExpressionInterface $expression): string
    {
        if ($expression instanceof ExpressionLiteral) {
            return $this->evaluateLiteral($expression);
        }

        $leftLiteral = $this->evaluateExpression($expression->getLeftExpression());
        $operation = $expression->getToken();
        $rightLiteral = $this->evaluateExpression($expression->getRightExpression());
        $this->logger->debug("Left literal ->:", [$leftLiteral]);
        $this->logger->debug("Operation ->:" . $operation->getLexeme());
        $this->logger->debug("Right literal ->:", [$rightLiteral]);

        return $this->evaluateLiteralBinaryOperation($leftLiteral, $operation, $rightLiteral);
    }

    private function evaluateLiteral(ExpressionLiteral $literal): string
    {
        $lexeme = $literal->getToken()->getLexeme();
        $result = $this->documentManager->findByToken($lexeme);
        $this->logger->debug(__METHOD__."  Result $result");
        return $result;
    }

    private function evaluateLiteralBinaryOperation(string $leftLiteral, TokenOperator $operator, string $rightLiteral): string
    {
        $result = $this->documentManager->executeBinaryOperation($leftLiteral, $operator, $rightLiteral);
        $this->logger->debug(__METHOD__.'  Result', [$result]);
        return $result;
    }


}
