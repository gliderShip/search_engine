<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $dbId;

    /**
     *  @Assert\Type(type="int", message="doc-id must be a positive integer")
     * @Assert\NotNull(message="please provide the doc-id")
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="array")
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
    private array $tokens = [];

    /**
     * @param $dbId
     * @param $id
     * @param array $tokens
     */
    public function __construct(int $dbId, int $id, array $tokens)
    {
        $this->dbId = $dbId;
        $this->id = $id;
        $this->tokens = $tokens;
    }

    public function getDbId(): ?int
    {
        return $this->dbId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTokens(): ?array
    {
        return $this->tokens;
    }


}
