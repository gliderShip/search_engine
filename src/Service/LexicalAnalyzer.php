<?php

namespace App\Service;

use App\Exception\BadExpressionException;
use App\Model\Lexeme;
use http\Exception\BadMethodCallException;

class LexicalAnalyzer
{

    /**
     * @throws BadExpressionException
     */
    public function getNextLexeme($command, $index = 0): ?Lexeme
    {
        $lexeme = null;

        if (empty($command)) {
            return null;
        }

        $firstLexemeChar = $command[$index];

        if ($firstLexemeChar == Lexeme::LEFT_BRACKET_OPERATOR) {
            $lexeme = new Lexeme(Lexeme::LEFT_BRACKET_OPERATOR);
            return $lexeme;
        }

        return $lexeme;
    }

    /**
     * @throws BadExpressionException
     */
    public function getClosingBracket(?Lexeme $lexeme, string $command): Lexeme
    {
        if ($lexeme->getType() != Lexeme::LEFT_BRACKET_OPERATOR) {
            throw new BadMethodCallException("Can not find matching closed bracket for lexeme type: " . $lexeme->getType());
        }

        $closingBracketPosition = $this->getClosingBracketPosition($command, $lexeme->getPosition());
        return new Lexeme(Lexeme::RIGHT_BRACKET_OPERATOR, Lexeme::RIGHT_BRACKET_OPERATOR, $closingBracketPosition);
    }

    /**
     * @throws BadExpressionException
     */
    private function getClosingBracketPosition(string $command, int $openBracketPosition): int
    {
        if ($command[$openBracketPosition] !== Lexeme::LEFT_BRACKET_OPERATOR) {
            throw new \BadMethodCallException("Command $command does not contain an open bracket at position $openBracketPosition");
        }

        $stack = new \SplStack();
        for ($i = $openBracketPosition; $i < strlen($command); ++$i) {
            $char = $command[$i];
            switch ($char) {
                case Lexeme::RIGHT_BRACKET_OPERATOR;
                    $stack->pop();
                    if ($stack->isEmpty()) {
                        return $i;
                    }
                    break;
                case Lexeme::LEFT_BRACKET_OPERATOR:
                    $stack->push($char);
                    break;
                default:
                    break;
            }
        }

        throw new BadExpressionException("Unclosed bracket at position $openBracketPosition while parsing expression ->:$command");
    }




}
