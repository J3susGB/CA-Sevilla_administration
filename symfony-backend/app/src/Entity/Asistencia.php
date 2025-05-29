<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AsistenciaRepository;
use App\Entity\ClaseSesion;
use App\Entity\Arbitros;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: AsistenciaRepository::class)]
class Asistencia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ClaseSesion::class, inversedBy: 'asistencias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClaseSesion $sesion = null;

    #[ORM\ManyToOne(targetEntity: Arbitros::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Arbitros $arbitro = null;

    #[ORM\Column(type: 'boolean')]
    private bool $asiste = false;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorias $categoria = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSesion(): ?ClaseSesion
    {
        return $this->sesion;
    }

    public function setSesion(ClaseSesion $sesion): static
    {
        $this->sesion = $sesion;
        return $this;
    }

    public function getArbitro(): ?Arbitros
    {
        return $this->arbitro;
    }

    public function setArbitro(Arbitros $arbitro): static
    {
        $this->arbitro = $arbitro;
        return $this;
    }

    public function isAsiste(): bool
    {
        return $this->asiste;
    }

    public function setAsiste(bool $asiste): static
    {
        $this->asiste = $asiste;
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
}
