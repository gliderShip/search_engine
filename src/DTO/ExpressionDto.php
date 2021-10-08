<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ExpressionDto
{
    /**
     * @Assert\Type(type="alnum", message="expression must be composed of alphanumeric tokens and the special symbols &, |, (, and ). ")
     * @Assert\NotBlank(message="please provide the expression")
     * @var string
     */
    private $expression;

    /**
     * @param string|null $expression
     */
    public function __construct(?string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * @param string|null $expression
     */
    public function setExpression(?string $expression): void
    {
        $this->expression = $expression;
    }


}
