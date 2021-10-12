<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class Parser
{
    public const AND_OPERATOR = '&';
    public const OR_OPERATOR = '|';
    public const LEFT_BRACKET_OPERATOR = '(';
    public const RIGHT_BRACKET_OPERATOR = ')';
    public const DELIMITERS = [
        ' '//space
    ];

    public const OPERATORS = [
        self::AND_OPERATOR,
        self::OR_OPERATOR,
        self::LEFT_BRACKET_OPERATOR,
        self::RIGHT_BRACKET_OPERATOR
    ];

    public const TOKENS = self::DELIMITERS + self::OPERATORS;


    /**
     * @var DocumentManager
     */
    private $documentManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(DocumentManager $documentManager, LoggerInterface $logger)
    {
        $this->documentManager = $documentManager;
        $this->logger = $logger;
    }

    private function evaluate($expression)
    {
        if ($this->isTerm($expression)) {
            return $this->documentManager->findByToken($expression);
        }

        dd($this->tokenize($expression));
        $leftArgument = $this->getLeftArgument($expression);
        $leftValue = $this->evaluate($leftArgument);

        $operator = $this->getOperator($expression);

        $rightArgument = $this->getRightArgument($expression);
        $rightValue = $this->evaluate($rightArgument);

        switch ($operator) {
            case self::AND_OPERATOR:
                return $this->documentManager->getDocumentsContainingAll($leftValue + $rightValue);
            case self::OR_OPERATOR:
                return $this->documentManager->getDocumentsContainingAny($leftValue + $rightValue);
        }
    }

    private function isTerm($expression)
    {
        if (ctype_alnum($expression)) {
            return true;
        }

    }

    public function tokenize(string $expression)
    {
        $expression = trim($expression);
        $tokensList = implode('', self::TOKENS);
        $lexemes = strtok($expression, $tokensList);
        dd($lexemes);
//        $firstChar = $expression[0];
//        if(ctype_alnum($firstChar)){
//            $term =
//        }
//        $expression = trim($expression);
//
//        $pattern = implode("", self::OPERATORS);
//        $this->logger->debug("Pattern:". $pattern);
//
//        $escapedPattern = '['.preg_quote($pattern).']';
//        $this->logger->debug("Escaped Pattern:". $escapedPattern);
//
////        $tokens = preg_split("/$escapedPattern/", $expression, -1, PREG_SPLIT_OFFSET_CAPTURE|PREG_SPLIT_DELIM_CAPTURE);
//        $tokens = [];
//        $result = preg_match_all("/$escapedPattern/", $expression, $tokens);
//        $this->logger->debug("Tokens", $tokens);
//
//        return $tokens;
        return explode(' ', $expression);

    }


}
