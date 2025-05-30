<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TestSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: TestSessionRepository::class)]
class TestSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"datetime_immutable")]
    private \DateTimeImmutable $fecha;

    #[ORM\Column(type:"integer")]
    private int $testNumber;

    #[ORM\ManyToOne(targetEntity: Categorias::class)]
    #[ORM\JoinColumn(nullable:false)]
    private Categorias $categoria;

    #[ORM\OneToMany(mappedBy:"session", targetEntity:Test::class, cascade:["persist","remove"])]
    private Collection $tests;

    public function __construct()
    {
        $this->tests = new ArrayCollection();
    }

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

    public function getTestNumber(): int
    {
        return $this->testNumber;
    }

    public function setTestNumber(int $testNumber): self
    {
        $this->testNumber = $testNumber;
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

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (! $this->tests->contains($test)) {
            $this->tests->add($test);
            $test->setSession($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // desvincular la sesión si fuera necesario
            if ($test->getSession() === $this) {
                $test->setSession(null);
            }
        }

        return $this;
    }

}
