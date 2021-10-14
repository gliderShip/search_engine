<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\Token;
use http\Exception\BadMethodCallException;

class TokenManager
{

    /**
     * @throws BadExpressionException
     */
    public function getClosingBracket(?Token $token, string $command): Token
    {
        if ($token->getType() != Token::LEFT_BRACKET_LEXEME) {
            throw new BadMethodCallException("Can not find matching closed bracket for token type: " . $token->getType());
        }

        $closingBracketPosition = $this->getClosingBracketPosition($command, $token->getPosition());
        return new Token(Token::RIGHT_BRACKET_LEXEME, Token::RIGHT_BRACKET_LEXEME, $closingBracketPosition);
    }

    /**
     * @throws BadExpressionException
     */
    private function getClosingBracketPosition(string $command, int $openBracketPosition): int
    {
        if ($command[$openBracketPosition] !== Token::LEFT_BRACKET_LEXEME) {
            throw new \BadMethodCallException("Command $command does not contain an open bracket at position $openBracketPosition");
        }

        $stack = new \SplStack();
        for ($i = $openBracketPosition; $i < strlen($command); ++$i) {
            $char = $command[$i];
            switch ($char) {
                case Token::RIGHT_BRACKET_LEXEME;
                    $stack->pop();
                    if ($stack->isEmpty()) {
                        return $i;
                    }
                    break;
                case Token::LEFT_BRACKET_LEXEME:
                    $stack->push($char);
                    break;
                default:
                    break;
            }
        }

        throw new BadExpressionException("Unclosed bracket at position $openBracketPosition while parsing expression ->:$command");
    }

    /**
     * @throws BadExpressionException
     */
    public function getLiteralTokenRightOperator(Token $token, $command): Token
    {
        if ($token->getType() != Token::TYPE_LITERAL) {
            throw new \BadMethodCallException("Unsupported Token type ->:" . $token->getType());
        }

        $literalEndIndex = $token->getEndPosition();
        $expectedDelimiter = $this->getNextToken($command, $literalEndIndex + 1);

        if (!$expectedDelimiter || !$expectedDelimiter->isSpace()) {
            throw new BadExpressionException("Token Delimiter expected at position ->:" . ($literalEndIndex + 1) . PHP_EOL . "Instead [" . $expectedDelimiter->getLexeme() . "] found!");
        }

        $rightToken = $this->getNextToken($command, $expectedDelimiter->getEndPosition() + 1);
        if (!$rightToken || !$rightToken->isOperator()) {
            throw new BadExpressionException("Operator Token expected at position ->:" . ($expectedDelimiter->getEndPosition() + 1) . PHP_EOL . "Instead [" . $rightToken->getLexeme() . "] found of type ->:" . $rightToken->getType());
        }

        return $rightToken;
    }

    /**
     * @throws BadExpressionException
     */
    public function getNextToken(string $command = null, int $index = 0): ?Token
    {
        $subcommand = substr($command, $index);
        if (empty($subcommand)) {
            return null;
        }

        $firstChar = $subcommand[0];
        $type = $this->getLexemeTokenType($firstChar);

        if ($type == null) {
            throw new BadExpressionException("Invalid character [$firstChar] at position:$index" . PHP_EOL . "Command ->:$command");
        }

        if ($type == Token::TYPE_LITERAL) {
            $lexeme = $this->getNextLexemeLiteral($subcommand);
        } else {
            $lexeme = $firstChar;
        }

        $token = new Token($lexeme, $index, $type);
        return $token;
    }

    /**
     * @param string $lexeme
     * @return string|null The token type
     */
    public function getLexemeTokenType(string $lexeme): ?string
    {
        if (in_array($lexeme, Token::BRACKETS)) {
            return Token::TYPE_BRACKET;
        } elseif (in_array($lexeme, Token::EXPRESSION_DELIMITERS)) {
            return Token::TYPE_SPACE;
        } elseif (in_array($lexeme, Token::OPERATORS)) {
            return Token::TYPE_OPERATOR;
        } elseif (!empty($lexeme) && ctype_alnum($lexeme)) {
            return Token::TYPE_LITERAL;
        }

        return null;
    }

    public function getNextLexemeLiteral($command, $index = 0): string
    {
        $lexeme = null;
        while (ctype_alnum($command[$index])) {
            $lexeme .= $command[$index++];
        }

        return $lexeme;
    }

    /**
     * @throws BadExpressionException
     */
    public function getOperatorRightLiteral(Token $operator, string $command): Token
    {
        if ($operator->getType() != Token::TYPE_OPERATOR) {
            throw new \BadMethodCallException("Unsupported Token type ->:" . $operator->getType());
        }

        $operatorIndex = $operator->getEndPosition();
        $expectedDelimiter = $this->getNextToken($command, $operatorIndex + 1);
        if (!$expectedDelimiter || !$expectedDelimiter->isSpace()) {
            throw new BadExpressionException("Token Delimiter expected at position ->:" . ($operatorIndex + 1) . PHP_EOL . "Instead [" . $expectedDelimiter->getLexeme() . "] found!");
        }

        $rightToken = $this->getNextToken($command, $expectedDelimiter->getEndPosition() + 1);
        if (!$rightToken || !$rightToken->isLiteral()) {
            throw new BadExpressionException("Literal Token expected at position ->:" . ($operatorIndex + 2) . PHP_EOL . "Instead [" . $rightToken->getLexeme() . "] found of type ->:" . $rightToken->getType());
        }
        return $rightToken;
    }


}
