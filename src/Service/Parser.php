<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\CompoundExpression;
use App\Model\ExpressionInterface;
use App\Model\ExpressionLiteral;
use App\Model\Token;
use App\Model\TokenBracket;
use App\Model\TokenLiteral;
use App\Model\TokenOperator;
use Psr\Log\LoggerInterface;

class Parser
{
    private LexicalAnalyzer $lexicalAnalyzer;
    private LoggerInterface $logger;

    public function __construct(LexicalAnalyzer $lexicalAnalyzer, LoggerInterface $logger)
    {
        $this->lexicalAnalyzer = $lexicalAnalyzer;
        $this->logger = $logger;
    }

    /**
     * @throws BadExpressionException
     */
    public function tokenize($command, int $index = 0): ?ExpressionInterface
    {
        $this->logger->debug("Command ->:[$command]    index ->:[$index]");

        if (empty($command)) {
            return null;
        }

        $index = $this->lexicalAnalyzer->skipSpaces($index, $command);

        $subCommand = substr($command, $index);
        if (empty($subCommand)) {
            return null;
        }

        if (TokenLiteral::isValidLexeme($subCommand)) {
            $token = new TokenLiteral($subCommand, $index);
            return new ExpressionLiteral($token, $index, $token->getEndPosition());
        }

        // Command is not a literal
        $leftToken = $this->lexicalAnalyzer->getNextToken($command, $index);
        if (!$leftToken) {
            throw new BadExpressionException("Literal or expression group () expected at position ->:$index Command ->$command");
        }
        $this->logger->debug('Left token', ['lexeme' => $leftToken->getLexeme(), 'position' => $leftToken->getStartPosition(), 'type' => $leftToken->getType(), 'command' => $command]);

        $leftChild = $this->getNextExpression($leftToken, $command);

        $index = $this->lexicalAnalyzer->skipSpaces($leftChild->getEndIndex() + 1, $command);
        $nextToken = $this->lexicalAnalyzer->getNextToken($command, $index);
        if (!$nextToken) {
            throw new BadExpressionException("Operator expected at position ->: $index Command ->$command");
        }

        if (!($nextToken instanceof TokenOperator)) {
            throw new BadExpressionException("Operator expected at position :" . $nextToken->getStartPosition() . PHP_EOL .
                "Found token [" . $nextToken->getLexeme() . "] of type:" . get_class($nextToken) . PHP_EOL .
                "Command ->$command"
            );
        }

        $operator = $nextToken; //rename
        $expression = new CompoundExpression($operator, $operator->getStartPosition(), $operator->getEndPosition());

        $index = $this->lexicalAnalyzer->skipSpaces($operator->getEndPosition() + 1, $command);
        $rightToken = $this->lexicalAnalyzer->getNextToken($command, $index);
        if (!$rightToken) {
            throw new BadExpressionException("Literal or expression group () expected at position ->:$index Command ->$command"
            );
        }

        $rightChild = $this->getNextExpression($rightToken, $command);

        $expression->setLeftExpression($leftChild);
        $expression->setRightExpression($rightChild);

        return $expression;
    }

    /**
     * @param Token $leftToken
     * @param string $command
     * @return ExpressionInterface
     * @throws BadExpressionException
     */
    private function getNextExpression(Token $leftToken, string $command): ExpressionInterface
    {
        if ($leftToken instanceof TokenLiteral) {
            $leftChild = new ExpressionLiteral($leftToken, $leftToken->getStartPosition(), $leftToken->getEndPosition());
        } elseif (($leftToken instanceof TokenBracket) && $leftToken->getLexeme() == TokenBracket::LEFT_BRACKET['LEXEME']) {
            $commandGroup = $this->getCommandGroup($leftToken, $command);
            $leftChild = $this->tokenize($commandGroup);
            $leftChild->setStartIndex($leftToken->getStartPosition());
            $leftChild->setEndIndex($leftToken->getEndPosition()+strlen($commandGroup)+1);
        } else {
            throw new BadExpressionException("Literal or expression group () expected at position :" . $leftToken->getStartPosition() . PHP_EOL .
                "Found token [" . $leftToken->getLexeme() . "] of type:" . $leftToken->getType() . PHP_EOL .
                "Command ->$command"
            );
        }

        return $leftChild;
    }

    /**
     * @throws BadExpressionException
     */
    private function getCommandGroup(TokenBracket $openBracket, string $command): string
    {
        $closingBracket = $this->lexicalAnalyzer->getClosingBracket($openBracket, $command);
        $groupStartIndex = $openBracket->getStartPosition() + 1;
        $groupLength = $closingBracket->getStartPosition() - $groupStartIndex;
        $commandGroup = substr($command, $groupStartIndex, $groupLength);

        $this->logger->debug("Command group ->: $commandGroup", ['start' => $groupStartIndex, 'length' => $groupLength]);

        return $commandGroup;
    }


}
