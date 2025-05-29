<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ClaseSesionRepository;
use App\Entity\Asistencia;
use App\Entity\Categorias;

#[ORM\Entity(repositoryClass: ClaseSesionRepository::class)]
class ClaseSesion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $fecha;

    #[ORM\Column(type: 'string', length: 20)]
    private string $tipo; // 'teorica' o 'practica'

    /**
     * Categoría de la sesión
     */
    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorias $categoria = null;

    /** @var Collection<int, Asistencia> */
    #[ORM\OneToMany(mappedBy: 'sesion', targetEntity: Asistencia::class, cascade: ['persist','remove'])]
    private Collection $asistencias;

    public function __construct()
    {
        $this->asistencias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): \DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;
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
     * @return Collection<int, Asistencia>
     */
    public function getAsistencias(): Collection
    {
        return $this->asistencias;
    }

    public function addAsistencia(Asistencia $asistencia): static
    {
        if (! $this->asistencias->contains($asistencia)) {
            $this->asistencias->add($asistencia);
            $asistencia->setSesion($this);
        }
        return $this;
    }

    public function removeAsistencia(Asistencia $asistencia): static
    {
        if ($this->asistencias->removeElement($asistencia)) {
            if ($asistencia->getSesion() === $this) {
                $asistencia->setSesion(null);
            }
        }
        return $this;
    }
}
