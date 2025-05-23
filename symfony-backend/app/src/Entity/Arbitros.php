<?php

namespace App\Entity;

use App\Repository\ArbitrosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: ArbitrosRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Arbitros
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $first_surname = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $second_surname = null;

    #[ORM\ManyToOne(targetEntity: Categorias::class, inversedBy: 'arbitros')]
    #[ORM\JoinColumn(name: 'categoria_id', nullable: false)]
    private ?Categorias $categoria = null;

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

    public function getFirstSurname(): ?string
    {
        return $this->first_surname;
    }

    public function setFirstSurname(string $first_surname): static
    {
        $this->first_surname = $first_surname;
        return $this;
    }

    public function getSecondSurname(): ?string
    {
        return $this->second_surname;
    }

    public function setSecondSurname(?string $second_surname): static
    {
        $this->second_surname = $second_surname;
        return $this;
    }

    public function getCategoria(): ?Categorias
    {
        return $this->categoria;
    }

    public function setCategoria(Categorias $categoria): static
    {
        $this->categoria = $categoria;
        return $this;
    }

    /**
     * Doctrine lifecycle callback antes de persistir o actualizar
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function uppercaseFields(): void
    {
        // Usamos mb_strtoupper para soportar acentos
        $this->name           = mb_strtoupper($this->name);
        $this->first_surname   = mb_strtoupper($this->first_surname);
        if ($this->second_surname !== null) {
            $this->second_surname = mb_strtoupper($this->second_surname);
        }
    }
}