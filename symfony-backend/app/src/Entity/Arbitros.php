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

    #[ORM\Column(type: 'string', length: 20, unique: true, nullable: true)]
    private ?string $nif = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $sexo = null;

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(string $sexo): static
    {
        $sexoNormalizado = mb_strtoupper($sexo);
        if (!in_array($sexoNormalizado, ['MASCULINO', 'FEMENINO'])) {
            throw new \InvalidArgumentException('Sexo inválido. Solo se permite MASCULINO o FEMENINO.');
        }

        $this->sexo = $sexoNormalizado;
        return $this;
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

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(string $nif): static
    {
        $this->nif = mb_strtoupper($nif);
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

        if ($this->nif !== null) {
            $this->nif = mb_strtoupper($this->nif);
        }

        if ($this->sexo !== null) {
            $this->sexo = mb_strtoupper($this->sexo);
        }
    }
}