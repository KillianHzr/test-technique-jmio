<?php
namespace App\Service;

use App\Dto\LinkedInProfileUrl;
use App\Entity\Freelance;
use App\Entity\FreelanceConso;
use App\Entity\FreelanceJeanPaul;
use App\Entity\FreelanceLinkedIn;
use Doctrine\ORM\EntityManagerInterface;

readonly class FreelanceConsolider
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function consolidate(Freelance $freelance): FreelanceConso
    {
        if (!$freelance->getFreelanceConso()) {
            $freelanceConso = new FreelanceConso();
            $freelanceConso->setFreelance($freelance);
            $freelance->setFreelanceConso($freelanceConso);
            $this->entityManager->persist($freelanceConso);
        } else {
            $freelanceConso = $freelance->getFreelanceConso();
        }

        $freelanceConso->setFullName($this->getFullname($freelance));
        $freelanceConso->setFirstName($this->getFirstName($freelance));
        $freelanceConso->setLastName($this->getLastName($freelance));
        $freelanceConso->setLinkedInUrl($this->getLinkedInUrl($freelance));
        $freelanceConso->setJobTitle($this->getJobTitle($freelance));

        return $freelanceConso;
    }

    private function getFullname(Freelance $freelance): ?string
    {
        $firstName = $this->getFirstName($freelance);
        $lastName = $this->getLastName($freelance);

        if ($firstName && $lastName) {
            return $firstName . ' ' . $lastName;
        }

        return $firstName ?? $lastName;
    }

    private function getFirstName(Freelance $freelance): ?string
    {
        /** @var FreelanceJeanPaul $freelanceJeanPaul */
        foreach ($freelance->getFreelanceJeanPauls() as $freelanceJeanPaul) {
            if ($freelanceJeanPaul->getFirstName()) {
                return $freelanceJeanPaul->getFirstName();
            }
        }

        foreach ($freelance->getFreelanceLinkedIns() as $freelanceLinkedIn) {
            if ($freelanceLinkedIn->getFirstName()) {
                return $freelanceLinkedIn->getFirstName();
            }
        }

        return null;
    }

    private function getLastName(Freelance $freelance): ?string
    {
        /** @var FreelanceJeanPaul $freelanceJeanPaul */
        foreach ($freelance->getFreelanceJeanPauls() as $freelanceJeanPaul) {
            if ($freelanceJeanPaul->getLastName()) {
                return $freelanceJeanPaul->getLastName();
            }
        }

        foreach ($freelance->getFreelanceLinkedIns() as $freelanceLinkedIn) {
            if ($freelanceLinkedIn->getLastName()) {
                return $freelanceLinkedIn->getLastName();
            }
        }

        return null;
    }

    private function getLinkedInUrl(Freelance $freelance): ?LinkedInProfileUrl
    {
        foreach ($freelance->getFreelanceLinkedIns() as $freelanceLinkedIn) {
            if ($freelanceLinkedIn->getUrl()) {
                return new LinkedInProfileUrl($freelanceLinkedIn->getUrl());
            }
        }

        return null;
    }

    private function getJobTitle(Freelance $freelance): ?string
    {
        $jobTitles = [];

        /** @var FreelanceJeanPaul $freelanceJeanPaul */
        foreach ($freelance->getFreelanceJeanPauls() as $freelanceJeanPaul) {
            $jobTitles[] = $freelanceJeanPaul->getJobTitle();
        }

        /** @var FreelanceLinkedIn $freelanceLinkedIn */
        foreach ($freelance->getFreelanceLinkedIns() as $freelanceLinkedIn) {
            $jobTitles[] = $freelanceLinkedIn->getJobTitle();
        }

        usort($jobTitles, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return array_shift($jobTitles);
    }
}