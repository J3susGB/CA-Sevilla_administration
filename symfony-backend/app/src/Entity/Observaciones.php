<?php

namespace App\Entity;

use App\Repository\ObservacionesRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: ObservacionesRepository::class)]
#[ORM\Table(name: "observaciones")]
class Observaciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Categorias $categoria;

    #[ORM\Column(type: 'string', length: 10)]
    private string $codigo;

    #[ORM\Column(type: 'text')]
    private string $descripcion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoria(): Categorias
    {
        return $this->categoria;
    }

    public function setCategoria(Categorias $categoria): self
    {
        $this->categoria = $categoria;
        return $this;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;
        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }
}
