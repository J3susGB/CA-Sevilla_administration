<?php

namespace App\Entity;

use App\Repository\SimulacrosRepository;
use App\Entity\Arbitros;
use App\Entity\Categorias;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimulacrosRepository::class)]
class Simulacros
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Arbitros::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Arbitros $arbitro;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Categorias $categoria;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $fecha;

    #[ORM\Column(type: 'float')]
    private float $periodo;

    // GETTERS Y SETTERS

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArbitro(): Arbitros
    {
        return $this->arbitro;
    }

    public function setArbitro(Arbitros $arbitro): self
    {
        $this->arbitro = $arbitro;
        return $this;
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

    public function getFecha(): \DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getPeriodo(): float
    {
        return $this->periodo;
    }

    public function setPeriodo(float $periodo): self
    {
        $this->periodo = $periodo;
        return $this;
    }
}
