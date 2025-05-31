<?php
// src/Entity/Tecnicos.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TecnicosRepository;

#[ORM\Entity(repositoryClass: TecnicosRepository::class)]
#[ORM\Table(name: "tecnicos")]
class Tecnicos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TecnicoSession::class, inversedBy: 'tecnicos')]
    #[ORM\JoinColumn(nullable: false)]
    private TecnicoSession $session;

    #[ORM\ManyToOne(targetEntity: Arbitros::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Arbitros $arbitro;

    #[ORM\Column(type: "float")]
    private float $nota;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $repesca = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): TecnicoSession
    {
        return $this->session;
    }

    public function setSession(TecnicoSession $session): self
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

    public function isRepesca(): bool
    {
        return $this->repesca;
    }

    /**
     * @param bool $repesca  Valor true = “sí repesca”; false = “no repesca”
     */
    public function setRepesca(bool $repesca): self
    {
        $this->repesca = $repesca;
        return $this;
    }
}
