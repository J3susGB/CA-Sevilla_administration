<?php

namespace App\Entity;

use App\Repository\EntrenamientosRepository;
use App\Entity\Arbitros;
use App\Entity\Categorias;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrenamientosRepository::class)]
class Entrenamientos
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

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $septiembre = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $octubre = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $noviembre = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $diciembre = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $enero = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $febrero = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $marzo = 0;

    #[ORM\Column(type: "integer", options: ["default" => 0])]
    private int $abril = 0;

    // Getters y Setters

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

    public function getSeptiembre(): int { return $this->septiembre; }
    public function setSeptiembre(int $v): self { $this->septiembre = $v; return $this; }

    public function getOctubre(): int { return $this->octubre; }
    public function setOctubre(int $v): self { $this->octubre = $v; return $this; }

    public function getNoviembre(): int { return $this->noviembre; }
    public function setNoviembre(int $v): self { $this->noviembre = $v; return $this; }

    public function getDiciembre(): int { return $this->diciembre; }
    public function setDiciembre(int $v): self { $this->diciembre = $v; return $this; }

    public function getEnero(): int { return $this->enero; }
    public function setEnero(int $v): self { $this->enero = $v; return $this; }

    public function getFebrero(): int { return $this->febrero; }
    public function setFebrero(int $v): self { $this->febrero = $v; return $this; }

    public function getMarzo(): int { return $this->marzo; }
    public function setMarzo(int $v): self { $this->marzo = $v; return $this; }

    public function getAbril(): int { return $this->abril; }
    public function setAbril(int $v): self { $this->abril = $v; return $this; }
}
