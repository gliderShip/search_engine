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
        $command = trim($command);
        if (empty($command)) {
            return null;
        }

        $subCommand = substr($command, $index);
        if (empty($subCommand)) {
            return null;
        }

        if (TokenLiteral::isValidLexeme($subCommand)) {
            $token = new TokenLiteral($subCommand, $index);
            return new ExpressionLiteral($token);
        }

        // Command is not a literal
        $leftToken = $this->lexicalAnalyzer->getNextToken($command, $index);
        if (!$leftToken) {
            throw new BadExpressionException("Literal or expression group () expected at position ->:$index Command ->$command");
        }
        $this->logger->debug('Left token', ['lexeme' => $leftToken->getLexeme(), 'position' => $leftToken->getPosition(), 'type' => $leftToken->getType(), 'command' => $command]);

        $leftChild = $this->getNextExpression($leftToken, $command);

        $newIndex = $this->lexicalAnalyzer->skipSpaces($leftToken->getEndPosition() + 1, $command);
        $nextToken = $this->lexicalAnalyzer->getNextToken($command, $newIndex);
        if (!$nextToken) {
            throw new BadExpressionException("Operator expected at position ->: $newIndex Command ->$command");
        }

        if (!($nextToken instanceof TokenOperator)) {
            throw new BadExpressionException("Operator expected at position :" . $nextToken->getPosition() . PHP_EOL .
                "Found token [" . $nextToken->getLexeme() . "] of type:" . get_class($nextToken) . PHP_EOL .
                "Command ->$command"
            );
        }

        $operator = $nextToken; //rename
        $expression = new CompoundExpression($operator);

        $newIndex = $this->lexicalAnalyzer->skipSpaces($operator->getEndPosition() + 1, $command);
        $rightToken = $this->lexicalAnalyzer->getNextToken($command, $newIndex);
        if (!$rightToken) {
            throw new BadExpressionException("Literal or expression group () expected at position ->:$newIndex Command ->$command"
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
            $leftChild = new ExpressionLiteral($leftToken);
        } elseif (($leftToken instanceof TokenBracket) && $leftToken->getLexeme() == TokenBracket::LEFT_BRACKET['LEXEME']) {
            $commandGroup = $this->getCommandGroup($leftToken, $command);
            $leftChild = $this->tokenize($commandGroup);
        } else {
            throw new BadExpressionException("Literal or expression group () expected at position :" . $leftToken->getPosition() . PHP_EOL .
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
        $groupStartIndex = $openBracket->getPosition() + 1;
        $groupLength = $closingBracket->getPosition() - $groupStartIndex;
        $commandGroup = substr($command, $groupStartIndex, $groupLength);

        $this->logger->debug("Command group ->: $commandGroup", ['start' => $groupStartIndex, 'length' => $groupLength]);

        return $commandGroup;
    }


}
