<?php

namespace App\Entity;

use App\Repository\SelectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SelectionRepository::class)]
class Selection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'selections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $recruiter = null;

    /**
     * @var Collection<int, FreelanceConso>
     */
    #[ORM\ManyToMany(targetEntity: FreelanceConso::class)]
    private Collection $freelances;

    public function __construct()
    {
        $this->freelances = new ArrayCollection();
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

    public function getRecruiter(): ?User
    {
        return $this->recruiter;
    }

    public function setRecruiter(?User $recruiter): static
    {
        $this->recruiter = $recruiter;

        return $this;
    }

    /**
     * @return Collection<int, FreelanceConso>
     */
    public function getFreelances(): Collection
    {
        return $this->freelances;
    }

    public function addFreelance(FreelanceConso $freelance): static
    {
        if (!$this->freelances->contains($freelance)) {
            $this->freelances->add($freelance);
        }

        return $this;
    }

    public function removeFreelance(FreelanceConso $freelance): static
    {
        $this->freelances->removeElement($freelance);

        return $this;
    }
}
