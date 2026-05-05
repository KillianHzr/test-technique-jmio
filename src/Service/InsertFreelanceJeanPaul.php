<?php
namespace App\Service;

use App\Dto\FreelanceJeanPaulDto;
use App\Entity\Freelance;
use App\Entity\FreelanceJeanPaul;
use Doctrine\ORM\EntityManagerInterface;

readonly class InsertFreelanceJeanPaul
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function insertFreelanceJeanPaul(FreelanceJeanPaulDto $dto): FreelanceJeanPaul
    {
        $freelanceJeanPaul = $this->entityManager->getRepository(FreelanceJeanPaul::class)->findOneBy(['jeanPaulId' => $dto->jeanPaulId]);
        if (!$freelanceJeanPaul) {
            $freelanceJeanPaul = new FreelanceJeanPaul();
            $freelanceJeanPaul->setJeanPaulId($dto->jeanPaulId);
            $this->entityManager->persist($freelanceJeanPaul);
        }

        if (!$freelanceJeanPaul->getFreelance()) {
            $freelance = new Freelance();
            $freelance->addFreelanceJeanPaul($freelanceJeanPaul);
            $this->entityManager->persist($freelance);
        }

        $freelanceJeanPaul->setFirstName($dto->firstName);
        $freelanceJeanPaul->setLastName($dto->lastName);
        $freelanceJeanPaul->setJobTitle($dto->jobTitle);

        return $freelanceJeanPaul;
    }
}