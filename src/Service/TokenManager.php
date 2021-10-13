<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\Lexeme;
use App\Model\Token;
use http\Exception\BadMethodCallException;

class TokenManager
{

    /**
     * @throws BadExpressionException
     */
    public function getNextToken($command, $index = 0): ?Token
    {
        $token = null;

        if (empty($command)) {
            return null;
        }

        $firstLexemeChar = $command[$index];

        if ($firstLexemeChar == Token::LEFT_BRACKET_OPERATOR) {
            $token = new Token(Token::LEFT_BRACKET_OPERATOR, Token::LEFT_BRACKET_OPERATOR, $index);
            return $token;
        }

        return $token;
    }

    /**
     * @throws BadExpressionException
     */
    public function getClosingBracket(?Token $token, string $command): Token
    {
        if ($token->getType() != Token::LEFT_BRACKET_OPERATOR) {
            throw new BadMethodCallException("Can not find matching closed bracket for token type: " . $token->getType());
        }

        $closingBracketPosition = $this->getClosingBracketPosition($command, $token->getPosition());
        return new Token(Token::RIGHT_BRACKET_OPERATOR, Token::RIGHT_BRACKET_OPERATOR, $closingBracketPosition);
    }

    /**
     * @throws BadExpressionException
     */
    private function getClosingBracketPosition(string $command, int $openBracketPosition): int
    {
        if ($command[$openBracketPosition] !== Token::LEFT_BRACKET_OPERATOR) {
            throw new \BadMethodCallException("Command $command does not contain an open bracket at position $openBracketPosition");
        }

        $stack = new \SplStack();
        for ($i = $openBracketPosition; $i < strlen($command); ++$i) {
            $char = $command[$i];
            switch ($char) {
                case Token::RIGHT_BRACKET_OPERATOR;
                    $stack->pop();
                    if ($stack->isEmpty()) {
                        return $i;
                    }
                    break;
                case Token::LEFT_BRACKET_OPERATOR:
                    $stack->push($char);
                    break;
                default:
                    break;
            }
        }

        throw new BadExpressionException("Unclosed bracket at position $openBracketPosition while parsing expression ->:$command");
    }

    public function getTermRightOperator(Token $token, $command)
    {
        $endIndex = $token->getEndPosition();
        $nextChar = $command[$endIndex+1];

        if(in_array($nextChar, Lexeme::LEXEME_DELIMITERS)){
            throw new BadExpressionException("Lexeme Delimiter expected at position".$endIndex+1);
        }

        $operator = $command[$endIndex+2];
        $lexeme = new Lexeme($operator, $endIndex+2);
    }

}
