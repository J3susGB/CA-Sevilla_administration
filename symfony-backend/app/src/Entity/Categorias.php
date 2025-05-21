<?php

namespace App\Entity;

use App\Repository\CategoriasRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriasRepository::class)]
class Categorias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'category')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Arbitros $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): ?Arbitros
    {
        return $this->category;
    }

    public function setCategory(?Arbitros $category): static
    {
        $this->category = $category;

        return $this;
    }
}
