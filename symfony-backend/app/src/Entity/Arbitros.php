<?php

namespace App\Entity;

use App\Repository\ArbitrosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArbitrosRepository::class)]
class Arbitros
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $first_surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $second_surname = null;

    /**
     * @var Collection<int, Categorias>
     */
    #[ORM\OneToMany(targetEntity: Categorias::class, mappedBy: 'category')]
    private Collection $category;

    public function __construct()
    {
        $this->category = new ArrayCollection();
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

    /**
     * @return Collection<int, Categorias>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Categorias $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
            $category->setCategory($this);
        }

        return $this;
    }

    public function removeCategory(Categorias $category): static
    {
        if ($this->category->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCategory() === $this) {
                $category->setCategory(null);
            }
        }

        return $this;
    }
}
