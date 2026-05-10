<?php
namespace App\Service;

use App\Dto\LinkedInProfileUrl;
use App\Entity\Freelance;
use App\Entity\FreelanceConso;
use App\Entity\FreelanceJeanPaul;
use App\Entity\FreelanceLinkedIn;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class FreelanceConsolider
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'fos_elastica.object_persister.freelance')]
        private ObjectPersisterInterface $persister,
    )
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

        $jobTitle = $freelanceConso->getJobTitle() ?? 'Freelance';
        $firstName = $freelanceConso->getFirstName() ?? 'Ce freelance';
        
        $skills = [];
        foreach ($freelance->getFreelanceLinkedIns() as $linkedIn) {
            $skills = array_merge($skills, $linkedIn->getSkills());
        }
        foreach ($freelance->getFreelanceJeanPauls() as $jeanPaul) {
            $skills = array_merge($skills, $jeanPaul->getSkills());
        }
        $freelanceConso->setSkills(array_values(array_unique(array_filter($skills))));
        
        $freelanceConso->setBio($this->generateBio($firstName, $jobTitle));

        $this->entityManager->flush();
        $this->persister->replaceOne($freelanceConso);

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

        $jobTitles = array_filter($jobTitles);

        usort($jobTitles, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        return array_shift($jobTitles);
    }

    private function generateSkills(string $jobTitle): array
    {
        $jobTitle = strtolower($jobTitle);
        $skills = ['Freelance'];

        if (str_contains($jobTitle, 'frontend') || str_contains($jobTitle, 'front-end') || str_contains($jobTitle, 'front')) {
            $skills = array_merge($skills, ['React', 'Vue.js', 'TypeScript', 'CSS/Tailwind', 'Next.js']);
        } 
        elseif (str_contains($jobTitle, 'backend') || str_contains($jobTitle, 'back-end') || str_contains($jobTitle, 'php')) {
            $skills = array_merge($skills, ['PHP', 'Symfony', 'Laravel', 'PostgreSQL', 'Redis']);
        }
        elseif (str_contains($jobTitle, 'analyste') || str_contains($jobTitle, 'logiciel') || str_contains($jobTitle, 'programmeur')) {
            $skills = array_merge($skills, ['Java', 'C#', 'SQL Architecture', 'UML', 'Algorithmie']);
        }
        elseif (str_contains($jobTitle, 'data') || str_contains($jobTitle, 'scient')) {
            $skills = array_merge($skills, ['Python', 'SQL', 'Machine Learning', 'Pandas', 'Spark']);
        }
        elseif (str_contains($jobTitle, 'devops') || str_contains($jobTitle, 'système') || str_contains($jobTitle, 'admin') || str_contains($jobTitle, 'cloud')) {
            $skills = array_merge($skills, ['Docker', 'Kubernetes', 'CI/CD', 'Linux', 'AWS/Azure']);
        }
        // Architect
        elseif (str_contains($jobTitle, 'architecte')) {
            $skills = array_merge($skills, ['Design Patterns', 'Microservices', 'Cloud Architecture', 'Scalability', 'Go']);
        }
        elseif (str_contains($jobTitle, 'projet') || str_contains($jobTitle, 'consultant')) {
            $skills = array_merge($skills, ['Agile', 'Scrum', 'Management', 'Product Strategy']);
        }
        elseif (str_contains($jobTitle, 'développeur') || str_contains($jobTitle, 'web')) {
            $skills = array_merge($skills, ['JavaScript', 'HTML/CSS', 'Git', 'Node.js', 'API REST']);
        }

        return array_values(array_unique($skills));
    }

    private function generateBio(string $firstName, string $jobTitle): string
    {
        return sprintf(
            "%s est un expert passionné spécialisé en tant que %s. Avec une solide expérience dans l'écosystème tech, il accompagne les entreprises dans leurs défis technologiques les plus complexes en apportant rigueur et innovation.",
            $firstName,
            $jobTitle
        );
    }
}