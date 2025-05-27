<?php

namespace App\Entity;

use App\Repository\CategoriasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Arbitros;
use App\Entity\Bonificaciones;

#[ORM\Entity(repositoryClass: CategoriasRepository::class)]
class Categorias
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Arbitros>
     */
    #[ORM\OneToMany(
        targetEntity: Arbitros::class,
        mappedBy: 'categoria',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $arbitros;

    /**
     * @var Collection<int, Bonificaciones>
     */
    #[ORM\OneToMany(
        targetEntity: Bonificaciones::class,
        mappedBy: 'categoria',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $bonificaciones;

    public function __construct()
    {
        $this->arbitros       = new ArrayCollection();
        $this->bonificaciones = new ArrayCollection();
    }

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

    /** @return Collection<int, Arbitros> */
    public function getArbitros(): Collection
    {
        return $this->arbitros;
    }

    public function addArbitro(Arbitros $arbitro): static
    {
        if (!$this->arbitros->contains($arbitro)) {
            $this->arbitros->add($arbitro);
            $arbitro->setCategoria($this);
        }

        return $this;
    }

    public function removeArbitro(Arbitros $arbitro): static
    {
        if ($this->arbitros->removeElement($arbitro)) {
            if ($arbitro->getCategoria() === $this) {
                $arbitro->setCategoria(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, Bonificaciones> */
    public function getBonificaciones(): Collection
    {
        return $this->bonificaciones;
    }

    public function addBonificacion(Bonificaciones $bonificacion): static
    {
        if (!$this->bonificaciones->contains($bonificacion)) {
            $this->bonificaciones->add($bonificacion);
            $bonificacion->setCategoria($this);
        }

        return $this;
    }

    public function removeBonificacion(Bonificaciones $bonificacion): static
    {
        if ($this->bonificaciones->removeElement($bonificacion)) {
            if ($bonificacion->getCategoria() === $this) {
                $bonificacion->setCategoria(null);
            }
        }

        return $this;
    }
}
