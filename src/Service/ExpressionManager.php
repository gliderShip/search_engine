<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\Expression;
use App\Model\Token;
use Psr\Log\LoggerInterface;

class ExpressionManager
{

    private TokenManager $tokenManager;
    private LoggerInterface $logger;
    private LexicalAnalyzer $lexicalAnalyzer;

    public function __construct(LexicalAnalyzer $lexicalAnalyzer, TokenManager $tokenManager, LoggerInterface $logger)
    {
        $this->tokenManager = $tokenManager;
        $this->logger = $logger;
        $this->lexicalAnalyzer = $lexicalAnalyzer;
    }

    /**
     * @throws BadExpressionException
     */
    public function tokenize($command, int $index = 0): ?Expression
    {
        $command = trim($command);
        if (empty($command)) {
            return null;
        }

        $subCommand = substr($command, $index);
        if (empty($subCommand)) {
            return null;
        }

        if (Token::isLexemeLiteral($subCommand)) {
            $token = new Token($subCommand, $index, Token::TYPE_LITERAL);
            $expression = new Expression($token);
            return $expression;
        }

        // Command is not a literal
        $firstToken = $this->tokenManager->getNextToken($command, $index);
        $leftToken = $this->skipSpaces($firstToken, $command);
        if (!$leftToken) {
            throw new BadExpressionException("Literal or expression group () expected at position :$index Command ->$command");
        }

        if ($leftToken->isLiteral()) {
            $leftChild = new Expression($leftToken);
        } elseif ($leftToken->isOpenBracket()) {
            $commandGroup = $this->getCommandGroup($leftToken, $command);
            $leftChild = $this->tokenize($commandGroup);
        } else {
            throw new BadExpressionException("Literal or expression group () expected at position :" . $leftToken->getPosition() . PHP_EOL .
                "Found token [" . $leftToken->getLexeme() . "] of type:" . $leftToken->getType() . PHP_EOL .
                "Command ->$command"
            );
        }

        $nextToken = $this->tokenManager->getNextToken($command, $leftChild->getToken()->getEndPosition() + 1);
        $operator = $this->skipSpaces($nextToken, $command);
        if (!$operator) {
            throw new BadExpressionException("Operator expected at position :".($leftChild->getToken()->getEndPosition() + 1)." Command ->$command");
        }

        if (!$operator->isOperator()) {
            throw new BadExpressionException("Operator expected at position :" . $nextToken->getPosition() . PHP_EOL .
                "Found token [" . $nextToken->getLexeme() . "] of type:" . $nextToken->getType() . PHP_EOL .
                "Command ->$command"
            );
        }

        $operator = $nextToken; //rename
        $expression = new Expression($operator);

        $rightOperand = $this->tokenManager->getNextToken($command, $operator->getEndPosition() + 1);
        $rightToken = $this->skipSpaces($rightOperand, $command);
        if (!$rightToken) {
            throw new BadExpressionException("Literal or expression group () expected at position :" . $operator->getEndPosition() + 1 . PHP_EOL .
                "Command ->$command"
            );
        }

        if ($rightToken->isLiteral()) {
            $rightChild = new Expression($rightToken);
        } elseif ($rightToken->isOpenBracket()) {
            $commandGroup = $this->getCommandGroup($rightToken, $command);
            $rightChild = $this->tokenize($commandGroup);
        } else {
            throw new BadExpressionException("Literal or expression group () expected at position :" . $rightToken->getPosition() . PHP_EOL .
                "Found token [" . $rightToken->getLexeme() . "] of type:" . $rightToken->getType() . PHP_EOL .
                "Command ->$command"
            );
        }

        $expression->setLeftExpression($leftChild);
        $expression->setRightExpression($rightChild);
        return $expression;
    }

    /**
     * @throws BadExpressionException
     */
    private function skipSpaces(Token $token = null, string $command): ?Token
    {
        while ($token && $token->isSpace()) {
            $token = $this->tokenManager->getNextToken($command, $token->getEndPosition() + 1);
        }

        return $token;
    }

    /**
     * @throws BadExpressionException
     */
    private function getCommandGroup(Token $openBracket, string $command): string
    {
        $closingBracket = $this->tokenManager->getClosingBracket($openBracket, $command);
        $groupStartIndex = $openBracket->getPosition() + 1;
        $groupLength = $closingBracket->getPosition() - $groupStartIndex;
        $commandGroup = substr($command, $groupStartIndex, $groupLength);

        $this->logger->debug("Command group ->: $commandGroup", ['start' => $groupStartIndex, 'length' => $groupLength]);

        return $commandGroup;
    }


}
