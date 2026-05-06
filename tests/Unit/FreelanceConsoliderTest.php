<?php

namespace App\Tests\Unit;

use App\Entity\Freelance;
use App\Entity\FreelanceJeanPaul;
use App\Entity\FreelanceLinkedIn;
use App\Service\FreelanceConsolider;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use PHPUnit\Framework\TestCase;

class FreelanceConsoliderTest extends TestCase
{
    private FreelanceConsolider $consolider;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $persister = $this->createMock(ObjectPersisterInterface::class);
        
        $this->consolider = new FreelanceConsolider($entityManager, $persister);
    }

    public function testConsolidationHeuristics(): void
    {
        $freelance = new Freelance();
        
        $jp = new FreelanceJeanPaul();
        $jp->setFirstName('Jean-Paul');
        $jp->setLastName('Gaultier');
        $jp->setJobTitle('Designer');
        $freelance->addFreelanceJeanPaul($jp);
        
        $li = new FreelanceLinkedIn();
        $li->setFirstName('Jean');
        $li->setLastName('Michel');
        $li->setJobTitle('Senior Fashion Designer & Creative Director');
        $li->setUrl('linkedin.com/in/jean-michel');
        $freelance->addFreelanceLinkedIn($li);
        
        $conso = $this->consolider->consolidate($freelance);
        
        $this->assertEquals('Jean-Paul', $conso->getFirstName());
        $this->assertEquals('Gaultier', $conso->getLastName());
        $this->assertEquals('Jean-Paul Gaultier', $conso->getFullName());
        
        $this->assertEquals('Senior Fashion Designer & Creative Director', $conso->getJobTitle());
        
        $this->assertEquals('linkedin.com/in/jean-michel', $conso->getLinkedInUrl());
    }

    public function testFallbackToLinkedInNames(): void
    {
        $freelance = new Freelance();
        
        $jp = new FreelanceJeanPaul();
        $freelance->addFreelanceJeanPaul($jp);
        
        $li = new FreelanceLinkedIn();
        $li->setFirstName('LinkedInName');
        $freelance->addFreelanceLinkedIn($li);
        
        $conso = $this->consolider->consolidate($freelance);
        
        $this->assertEquals('LinkedInName', $conso->getFirstName());
    }
}
