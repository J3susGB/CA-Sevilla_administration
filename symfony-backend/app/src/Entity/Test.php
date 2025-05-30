<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TestRepository;

#[ORM\Entity(repositoryClass: TestRepository::class)]
class Test
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TestSession::class, inversedBy: 'tests')]
    #[ORM\JoinColumn(nullable: false)]
    private TestSession $session;

    #[ORM\ManyToOne(targetEntity: Arbitros::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Arbitros $arbitro;

    #[ORM\Column(type:"float")]
    private float $nota;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Categorias $categoria;

        public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): TestSession
    {
        return $this->session;
    }

    public function setSession(TestSession $session): self
    {
        $this->session = $session;
        return $this;
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

    public function getNota(): float
    {
        return $this->nota;
    }

    public function setNota(float $nota): self
    {
        $this->nota = $nota;
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

}
