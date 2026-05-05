<?php
namespace App\Service;

use App\Dto\FreelanceLinkedInDto;
use App\Dto\LinkedInProfileUrl;
use App\Entity\Freelance;
use App\Entity\FreelanceLinkedIn;
use Doctrine\ORM\EntityManagerInterface;

readonly class InsertFreelanceLinkedIn
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function insertFreelanceLinkedIn(FreelanceLinkedInDto $dto): FreelanceLinkedIn
    {
        $linkedInUrl = new LinkedInProfileUrl($dto->url);

        $freelanceLinkedIn = $this->entityManager->getRepository(FreelanceLinkedIn::class)->findOneBy(['url' => $linkedInUrl]);
        if (!$freelanceLinkedIn) {
            $now = new \DateTime();
            $freelanceLinkedIn = new FreelanceLinkedIn();
            $freelanceLinkedIn->setUrl($linkedInUrl);
            $freelanceLinkedIn->setCreatedAt($now);
            $freelanceLinkedIn->setUpdatedAt($now);
            $this->entityManager->persist($freelanceLinkedIn);
        }

        if (!$freelanceLinkedIn->getFreelance()) {
            $freelance = new Freelance();
            $freelance->addFreelanceLinkedIn($freelanceLinkedIn);
            $this->entityManager->persist($freelance);
        }

        $freelanceLinkedIn->setFirstName($dto->firstName);
        $freelanceLinkedIn->setLastName($dto->lastName);
        $freelanceLinkedIn->setJobTitle($dto->jobTitle);

        return $freelanceLinkedIn;
    }
}