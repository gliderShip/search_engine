<?php

namespace App\Service;

use App\DTO\CollectionDto;
use App\Entity\Collection;
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
        $this->parser = $parser;
        $this->logger = $logger;
    }

    /**
     * @throws BadExpressionException
     * @throws \Exception
     */
    public function execute($command): CollectionDto
    {
        $this->logger->debug("Parsing Command ->:" . $command);

        $syntaxTree = $this->parser->tokenize($command);
        $this->logger->debug("Syntax Tree ->:" . print_r($syntaxTree, true));

        if ($syntaxTree) {
            $documentCollection = $this->evaluateExpression($syntaxTree);
            $collectionDto = $this->documentManager->getCollectionDto($documentCollection);

            $this->logger->debug(__METHOD__ . '  Result', [$documentCollection]);
        } else {
            throw new \Exception("Unexpected error occurred while parsing command ->:$command");
        }

        return $collectionDto;
    }

    private function evaluateExpression(ExpressionInterface $expression): Collection
    {
        if ($expression instanceof ExpressionLiteral) {
            return $this->evaluateLiteral($expression);
        }

        $leftDocumentCollection = $this->evaluateExpression($expression->getLeftExpression());
        $operation = $expression->getToken();
        $rightDocumetCollection = $this->evaluateExpression($expression->getRightExpression());
        $this->logger->debug("Left literal ->:", [$leftDocumentCollection]);
        $this->logger->debug("Operation ->:" . $operation->getLexeme());
        $this->logger->debug("Right literal ->:", [$rightDocumetCollection]);

        return $this->evaluateBinaryOperation($leftDocumentCollection, $operation, $rightDocumetCollection);
    }

    private function evaluateLiteral(ExpressionLiteral $literal): Collection
    {
        $lexeme = $literal->getToken()->getLexeme();
        $result = $this->documentManager->getDocumentsByKeyword($lexeme);
        $this->logger->debug(__METHOD__ . "  Result ->", ['Result Id:' => $result->getId(), 'content' => $result->getContent()]);
        return $result;
    }

    public function evaluateBinaryOperation(Collection $leftDocumentCollection, TokenOperator $operator, Collection $rightDocumentCollection): Collection
    {
        $operation = $operator->getLexeme();
        switch ($operation) {
            case TokenOperator::AND_OPERATOR['LEXEME']:
                $result = $this->documentManager->getCommonDocuments([$leftDocumentCollection, $rightDocumentCollection]);
                break;
            case TokenOperator::OR_OPERATOR['LEXEME']:
                $result = $this->documentManager->getAllDocuments([$leftDocumentCollection, $rightDocumentCollection]);
                break;
            default:
                throw new \BadMethodCallException("Unsupported operation ->:" . $operation);
        }


        return $result;
    }


}
