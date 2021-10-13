<?php

namespace App\Service;

use App\Model\Token;
use Psr\Log\LoggerInterface;

class Parser
{
    public const AND_OPERATOR = '&';
    public const OR_OPERATOR = '|';
    public const LEFT_BRACKET_OPERATOR = '(';
    public const RIGHT_BRACKET_OPERATOR = ')';
    public const EXPRESSION_DELIMITERS = [
        ' '//space
    ];

    public const OPERATORS = [
        self::AND_OPERATOR,
        self::OR_OPERATOR,
        self::LEFT_BRACKET_OPERATOR,
        self::RIGHT_BRACKET_OPERATOR
    ];

    public const LEXEMES = self::EXPRESSION_DELIMITERS + self::OPERATORS;


    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var ExpressionManager
     */
    private $expressionService;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(DocumentManager $documentManager, ExpressionManager $expressionService, LoggerInterface $logger)
    {
        $this->documentManager = $documentManager;
        $this->logger = $logger;
        $this->expressionService = $expressionService;
    }

    private function evaluate($expression)
    {
        $expressionModel  = $this->expressionService->tokenize($expression);
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
        $expression =
        if(empty($expression)){
            return null;
        }

        if($this->isTerm($expression)){
            $token = new Token();
            $token->setLexeme($expression);
            $token->setType(Token::TERM);
            return $token;
        }

        $length = strlen($expression);
        $token = getNextToken($expression);


//        $tokensList = implode('', self::LEXEMES);
//        $lexemes = strtok($expression, $tokensList);
//        dd($lexemes);
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


    private function getNextToken(string $expression){
        if(empty($expression)){
            return null;
        }

        $firstChar = $expression[0];
        switch ()
        if(in_array($firstChar, self::OPERATORS){
            $token = new Token($firstChar, Token::)
        }

    }


}
