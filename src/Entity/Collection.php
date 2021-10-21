<?php

namespace App\Entity;

use App\Repository\CollectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CollectionRepository::class)
 */
class Collection
{

    /**
     * @ORM\Id
     *  @Assert\Type(type="string", message="collection-id must be a string")
     * @Assert\NotNull(message="please provide the collection-id")
     * @ORM\Column(type="string")
     */
    private string $id;

    /**
     * @ORM\Column(type="array")
     * @Assert\NotNull(message="The collection content can not be null")
     */
    private $content = [];

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
