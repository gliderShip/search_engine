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
        if(empty($command)){
            return null;
        }

        if($this->lexicalAnalyzer->isTerm($command, $index)){
            $token = new Token($command, Token::TERM, $index);
            $expression = new Expression($token);
            return $expression;
        }

        $lexeme = $this->lexicalAnalyzer->getNextLexeme($command, $index);
        if($lexeme == null){
            return null;
        }

        if($this->lexicalAnalyzer->isTerm($lexeme)){
            $token = new Token($lexeme, Token::TERM, $index);
            $leftExpression = new Expression($token);
            $operator = $this->tokenManager->getTermRightOperator($token, $command);
        }

        if ($this->isTerm($command)) {
            $token = new Token($command, Token::TERM, $index);
            $expression = new Expression($token);
            return $expression;
        }



        $token = $this->tokenManager->getNextToken($command);
        if ($token->getType() == Token::LEFT_BRACKET_OPERATOR) {

            $commandGroup = $this->getCommandGroup($token, $command);
            $leftExpression = $this->tokenize($commandGroup);
            $operator = $this->getLeftOperator($commandGroup, $command);
            $expression = $this->tokenize($commandGroup);

            $expression = new Expression();

        }


    }

    private function isTerm($expression): bool
    {
        //TODO
        return true;
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

    private function getLeftOperator(string $commandGroup, $command)
    {

    }
}
