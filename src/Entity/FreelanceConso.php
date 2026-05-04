<?php

namespace App\Entity;

use App\Repository\FreelanceConsoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FreelanceConsoRepository::class)]
class FreelanceConso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['freelance_detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?string $jobTitle = null;

    #[ORM\OneToOne(inversedBy: 'freelanceConso', cascade: ['persist', 'remove'])]
    private ?Freelance $freelance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?string $linkedInUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?string $fullName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getFreelance(): ?Freelance
    {
        return $this->freelance;
    }

    public function setFreelance(?Freelance $freelance): static
    {
        $this->freelance = $freelance;

        return $this;
    }

    public function getLinkedInUrl(): ?string
    {
        return $this->linkedInUrl;
    }

    public function setLinkedInUrl(?string $linkedInUrl): static
    {
        $this->linkedInUrl = $linkedInUrl;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }
}
