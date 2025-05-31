<?php
// src/Entity/TecnicoSession.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\TecnicoSessionRepository;

#[ORM\Entity(repositoryClass: TecnicoSessionRepository::class)]
#[ORM\Table(name: "tecnico_session")]
class TecnicoSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "date_immutable")]
    private \DateTimeImmutable $fecha;

    #[ORM\Column(type: "integer")]
    private int $examNumber;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Categorias $categoria;

    #[ORM\OneToMany(mappedBy: "session", targetEntity: Tecnicos::class, cascade: ["persist","remove"], orphanRemoval: true)]
    private Collection $tecnicos;

    public function __construct()
    {
        $this->tecnicos = new ArrayCollection();
    }

    // … getters y setters …

    public function getId(): ?int
    {
        return $this->id;
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

    public function getExamNumber(): int
    {
        return $this->examNumber;
    }

    public function setExamNumber(int $num): self
    {
        $this->examNumber = $num;
        return $this;
    }

    public function getCategoria(): Categorias
    {
        return $this->categoria;
    }

    public function setCategoria(Categorias $cat): self
    {
        $this->categoria = $cat;
        return $this;
    }

    /**
     * @return Collection|Tecnicos[]
     */
    public function getTecnicos(): Collection
    {
        return $this->tecnicos;
    }

    public function addTecnico(Tecnicos $t): self
    {
        if (! $this->tecnicos->contains($t)) {
            $this->tecnicos->add($t);
            $t->setSession($this);
        }
        return $this;
    }

    public function removeTecnico(Tecnicos $t): self
    {
        if ($this->tecnicos->removeElement($t)) {
            // rompe la relación inversa
            if ($t->getSession() === $this) {
                $t->setSession(null);
            }
        }
        return $this;
    }
}
