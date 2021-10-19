<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CollectionDto
{
    /**
     *  @Assert\Type(type="string", message="collection-id must be a string")
     * @Assert\NotNull(message="please provide the collection-id")
     */
    private string $id;

    /**
     * @Assert\NotNull(message="The collection content can not be null")
     */
    private array $content = [];

    /**
     * @param $id
     * @param array $content
     */
    public function __construct(string $id, array $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }
}
