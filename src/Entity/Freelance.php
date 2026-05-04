<?php

namespace App\Entity;

use App\Repository\FreelanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FreelanceRepository::class)]
class Freelance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['freelance_detail'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['freelance_detail'])]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, FreelanceLinkedIn>
     */
    #[ORM\OneToMany(targetEntity: FreelanceLinkedIn::class, mappedBy: 'freelance')]
    #[Groups(['freelance_detail'])]
    private Collection $freelanceLinkedIns;

    /**
     * @var Collection<int, FreelanceJeanPaul>
     */
    #[ORM\OneToMany(targetEntity: FreelanceJeanPaul::class, mappedBy: 'freelance')]
    #[Groups(['freelance_detail'])]
    private Collection $freelanceJeanPauls;

    #[ORM\OneToOne(mappedBy: 'freelance', cascade: ['persist', 'remove'])]
    #[Groups(['freelance_detail'])]
    private ?FreelanceConso $freelanceConso = null;

    public function __construct()
    {
        $this->freelanceLinkedIns = new ArrayCollection();
        $this->freelanceJeanPauls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, FreelanceLinkedIn>
     */
    public function getFreelanceLinkedIns(): Collection
    {
        return $this->freelanceLinkedIns;
    }

    public function addFreelanceLinkedIn(FreelanceLinkedIn $freelanceLinkedIn): static
    {
        if (!$this->freelanceLinkedIns->contains($freelanceLinkedIn)) {
            $this->freelanceLinkedIns->add($freelanceLinkedIn);
            $freelanceLinkedIn->setFreelance($this);
        }

        return $this;
    }

    public function removeFreelanceLinkedIn(FreelanceLinkedIn $freelanceLinkedIn): static
    {
        if ($this->freelanceLinkedIns->removeElement($freelanceLinkedIn)) {
            // set the owning side to null (unless already changed)
            if ($freelanceLinkedIn->getFreelance() === $this) {
                $freelanceLinkedIn->setFreelance(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FreelanceJeanPaul>
     */
    public function getFreelanceJeanPauls(): Collection
    {
        return $this->freelanceJeanPauls;
    }

    public function addFreelanceJeanPaul(FreelanceJeanPaul $freelanceJeanPaul): static
    {
        if (!$this->freelanceJeanPauls->contains($freelanceJeanPaul)) {
            $this->freelanceJeanPauls->add($freelanceJeanPaul);
            $freelanceJeanPaul->setFreelance($this);
        }

        return $this;
    }

    public function removeFreelanceJeanPaul(FreelanceJeanPaul $freelanceJeanPaul): static
    {
        if ($this->freelanceJeanPauls->removeElement($freelanceJeanPaul)) {
            // set the owning side to null (unless already changed)
            if ($freelanceJeanPaul->getFreelance() === $this) {
                $freelanceJeanPaul->setFreelance(null);
            }
        }

        return $this;
    }

    public function getFreelanceConso(): ?FreelanceConso
    {
        return $this->freelanceConso;
    }

    public function setFreelanceConso(?FreelanceConso $freelanceConso): static
    {
        // unset the owning side of the relation if necessary
        if ($freelanceConso === null && $this->freelanceConso !== null) {
            $this->freelanceConso->setFreelance(null);
        }

        // set the owning side of the relation if necessary
        if ($freelanceConso !== null && $freelanceConso->getFreelance() !== $this) {
            $freelanceConso->setFreelance($this);
        }

        $this->freelanceConso = $freelanceConso;

        return $this;
    }
}
