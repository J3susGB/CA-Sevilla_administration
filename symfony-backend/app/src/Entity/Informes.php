<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InformesRepository;
use App\Entity\Arbitros;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: InformesRepository::class)]
class Informes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Arbitros::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Arbitros $arbitro;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Categorias $categoria;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $fecha;

    #[ORM\Column(type: "float")]
    private float $nota;

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

    public function getFecha(): \DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): self
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getNota(): float
    {
        return $this->nota;
    }

    public function setNota(float $nota): self
    {
        $this->nota = $nota;
        return $this;
    }
}
