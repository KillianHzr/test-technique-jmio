<?php

namespace App\Tests;

use App\Service\FreelanceSearchService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SearchFeaturesTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $serializer = $container->get('serializer');
        $insertLinkedIn = $container->get(\App\Service\InsertFreelanceLinkedIn::class);
        $consolider = $container->get(\App\Service\FreelanceConsolider::class);
        
        $jsonData = file_get_contents('./datas/linkedin.json');
        $linkedInDtos = $serializer->deserialize($jsonData, \App\Dto\FreelanceLinkedInDto::class . '[]', 'json');
        
        for ($i = 0; $i < 5; $i++) {
            $f = $insertLinkedIn->insertFreelanceLinkedIn($linkedInDtos[$i]);
            $em->flush();
            $consolider->consolidate($f->getFreelance());
        }

        $index = $container->get('fos_elastica.index.freelance');
        $index->refresh();
    }

    public function testAutocompleteSuggestion(): void
    {
        self::bootKernel();
        $searchService = static::getContainer()->get(FreelanceSearchService::class);

        $suggestion = $searchService->getAutocompleteSuggestion('Jea');
        $this->assertNotEmpty($suggestion);
        $this->assertStringStartsWith('Jea', $suggestion);
        $this->assertEquals('Jean Martin', $suggestion);

        $suggestionReverse = $searchService->getAutocompleteSuggestion('Mar');
        $this->assertNotEmpty($suggestionReverse);
        $this->assertEquals('Martin Jean', $suggestionReverse);
    }

    public function testMatchingProfiles(): void
    {
        self::bootKernel();
        $searchService = static::getContainer()->get(FreelanceSearchService::class);
        $em = static::getContainer()->get('doctrine')->getManager();

        $freelance = $em->getRepository(\App\Entity\FreelanceConso::class)->findOneBy(['firstName' => 'Jean', 'lastName' => 'Martin']);
        $this->assertNotNull($freelance, 'Jean Martin must exist for this test');

        $matches = $searchService->getMatchingFreelances($freelance->getId(), 3);
        
        $this->assertIsArray($matches);
        $this->assertLessThanOrEqual(3, count($matches));
        
        foreach ($matches as $match) {
            $this->assertInstanceOf(\App\Entity\FreelanceConso::class, $match);
            $this->assertNotEquals($freelance->getId(), $match->getId(), 'Matching should not return the same profile');
        }
    }
}
