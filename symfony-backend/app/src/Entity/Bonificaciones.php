<?php

namespace App\Entity;

use App\Repository\BonificacionesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: BonificacionesRepository::class)]
class Bonificaciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $valor = null;

    #[ORM\ManyToOne(targetEntity: Categorias::class, inversedBy: 'bonificaciones')]
    #[ORM\JoinColumn(nullable: false)]
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

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): static
    {
        $this->valor = $valor;

        return $this;
    }

    public function getCategoria(): ?Categorias
    {
        return $this->categoria;
    }

    public function setCategoria(?Categorias $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }
}