<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\Token;
use App\Model\TokenBracket;
use App\Model\TokenInterface;
use App\Model\TokenLiteral;
use App\Model\TokenOperator;
use App\Model\TokenSpace;
use http\Exception\BadMethodCallException;

class LexicalAnalyzer
{
    /**
     * @throws BadExpressionException
     */
    public function getClosingBracket(?Token $token, string $command): TokenBracket
    {
        if (!($token instanceof TokenBracket) || ($token->getLexeme() != TokenBracket::LEFT_BRACKET['LEXEME'])) {
            throw new BadMethodCallException("Can not find matching closed bracket for token: " . $token->getType() . " lexeme->[" . $token->getLexeme() . "]");
        }

        $closingBracketPosition = $this->getClosingBracketPosition($command, $token->getStartPosition());
        return new TokenBracket(TokenBracket::LEFT_BRACKET['LEXEME'], $closingBracketPosition);
    }

    /**
     * @throws BadExpressionException
     */
    public function getNextToken(string $command = null, int $index = 0): ?TokenInterface
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

        if ($type == TokenLiteral::class) {
            $lexeme = $this->getNextLexemeLiteral($subcommand);
        } else {
            $lexeme = $firstChar;
        }

        $token = new $type($lexeme, $index);
        return $token;
    }

    public function skipSpaces(int $index, string $command): int
    {
        $spaces = TokenSpace::EXPRESSION_DELIMITERS;

        while (in_array(substr($command, $index, 1), $spaces)) {
            ++$index;
        }

        return $index;
    }

    /**
     * @throws BadExpressionException
     */
    private function getClosingBracketPosition(string $command, int $openBracketPosition): int
    {
        if ($command[$openBracketPosition] !== TokenBracket::LEFT_BRACKET['LEXEME']) {
            throw new \BadMethodCallException("Command $command does not contain an open bracket at position $openBracketPosition");
        }

        $stack = new \SplStack();
        for ($i = $openBracketPosition; $i < strlen($command); ++$i) {
            $char = $command[$i];
            switch ($char) {
                case TokenBracket::RIGHT_BRACKET['LEXEME'];
                    $stack->pop();
                    if ($stack->isEmpty()) {
                        return $i;
                    }
                    break;
                case TokenBracket::LEFT_BRACKET['LEXEME']:
                    $stack->push($char);
                    break;
                default:
                    break;
            }
        }

        throw new BadExpressionException("Unclosed bracket at position $openBracketPosition while parsing expression ->:$command");
    }

    /**
     * @param string $lexeme
     * @return string|null The token type
     */
    private function getLexemeTokenType(string $lexeme): ?string
    {
        if (TokenBracket::isValidLexeme($lexeme)) {
            return TokenBracket::class;
        } elseif (TokenSpace::isValidLexeme($lexeme)) {
            return TokenSpace::class;
        } elseif (TokenOperator::isValidLexeme($lexeme)) {
            return TokenOperator::class;
        } elseif (!empty($lexeme) && TokenLiteral::isValidLexeme($lexeme)) {
            return TokenLiteral::class;
        }

        return null;
    }

    private function getNextLexemeLiteral(?string $command, $index = 0): string
    {
        $lexeme = null;
        while (isset($command[$index]) && ctype_alnum($command[$index])) {
            $lexeme .= $command[$index++];
        }

        return $lexeme;
    }

}
