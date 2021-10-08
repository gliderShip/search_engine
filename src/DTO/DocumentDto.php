<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class DocumentDto
{
    /**
     *  @Assert\Type(type="int", message="doc-id must be a positive integer")
     * @Assert\NotNull(message="please provide the doc-id")
     * @var integer
     */
    private $id;

    /**
     * @var array
     * @Assert\NotNull(message="You must specify at least one token")
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one token",
     * )
     * @Assert\All({
     *     @Assert\NotBlank,
     *      @Assert\Type(type="alnum", message="tokens must be alfanumeric strings")
     * })
     */
    private $tokens = [];

    /**
     * @param int $id
     * @param array $tokens
     */
    public function __construct($id = null, $tokens = null)
    {
        if(is_numeric($id)){
            $this->id = intval($id);
        }

        $this->tokens = $tokens;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id = null): void
    {
        if(is_numeric($id)){
            $this->id = intval($id);
        }
    }

    public function getTokens(): ?array
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens = []): self
    {
        $this->tokens = $tokens;

        return $this;
    }
}
