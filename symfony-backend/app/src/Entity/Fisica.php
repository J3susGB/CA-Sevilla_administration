<?php
// src/Entity/Fisica.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FisicaRepository;
use App\Entity\Arbitros;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: FisicaRepository::class)]
#[ORM\Table(name: "fisica")]
class Fisica
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

    #[ORM\Column(type: 'integer')]
    private int $convocatoria;

    #[ORM\Column(type: 'boolean', options: ['default' => false], nullable: true)]
    private ?bool $repesca = false;

    #[ORM\Column(type: 'float')]
    private float $yoyo;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $velocidad = null;

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

    public function getConvocatoria(): int
    {
        return $this->convocatoria;
    }

    public function setConvocatoria(int $convocatoria): self
    {
        $this->convocatoria = $convocatoria;
        return $this;
    }

    public function getRepesca(): ?bool
    {
        return $this->repesca;
    }

    public function setRepesca(?bool $repesca): self
    {
        $this->repesca = $repesca ?? false;
        return $this;
    }

    public function getYoyo(): float
    {
        return $this->yoyo;
    }

    public function setYoyo(float $yoyo): self
    {
        $this->yoyo = $yoyo;
        return $this;
    }

    public function getVelocidad(): ?float
    {
        return $this->velocidad;
    }

    public function setVelocidad(?float $velocidad): self
    {
        $this->velocidad = $velocidad;
        return $this;
    }

}
