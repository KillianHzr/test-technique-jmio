<?php

namespace App\Tests;

use App\Dto\FreelanceJeanPaulDto;
use App\Dto\FreelanceLinkedInDto;
use App\Entity\Freelance;
use App\Service\FreelanceConsolider;
use App\Service\FreelanceManager;
use App\Service\FreelanceSearchService;
use App\Service\FreelanceSerializer;
use App\Service\InsertFreelanceJeanPaul;
use App\Service\InsertFreelanceLinkedIn;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TechnicalTest extends KernelTestCase
{
    public function testEnvDocker(): void
    {
        self::bootKernel();

        $isDocker = static::getContainer()->getParameter('is_docker');

        $this->assertTrue($isDocker);
    }

    public function testImportLinkedIn(): void
    {
        self::bootKernel();
        $serializer = static::getContainer()->get('serializer');
        $insertData = static::getContainer()->get(InsertFreelanceLinkedIn::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $jsonData = file_get_contents('./datas/linkedin.json');

        $linkedInDtos = $serializer->deserialize($jsonData, FreelanceLinkedInDto::class . '[]', 'json');
        $this->assertNotEmpty($linkedInDtos, '$linkedInDtos is null');

        foreach ($linkedInDtos as $linkedInDto) {
            $fLinkedIn = $insertData->insertFreelanceLinkedIn($linkedInDto);

            $this->assertNotNull($fLinkedIn, 'FreelanceLinkedIn is null');
            $this->assertNotNull($fLinkedIn->getCreatedAt(), '$fLinkedIn::createdAt is null');
            $this->assertNotNull($fLinkedIn->getUpdatedAt(), '$fLinkedIn::updatedAt is null');
            $this->assertTrue($fLinkedIn->getUpdatedAt() < Carbon::now());

        }

        $entityManager->flush();
    }

    public function testImportJeanPaul(): void
    {
        self::bootKernel();
        $serializer = static::getContainer()->get('serializer');
        $insertData = static::getContainer()->get(InsertFreelanceJeanPaul::class);
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $jsonData = file_get_contents('./datas/jean-paul.json');

        $jeanPaulDtos = $serializer->deserialize($jsonData, FreelanceJeanPaulDto::class . '[]', 'json');
        $this->assertNotEmpty($jeanPaulDtos, '$jeanPaulDtos is null');


        foreach ($jeanPaulDtos as $jeanPaulDto) {
            $fJeanPaul = $insertData->insertFreelanceJeanPaul($jeanPaulDto);

            $this->assertNotNull($fJeanPaul, 'FreelanceJeanPaul is null');
            $this->assertNotNull($fJeanPaul->getCreatedAt(), '$fJeanPaul::createdAt is null');
            $this->assertNotNull($fJeanPaul->getUpdatedAt(), '$fJeanPaul::updatedAt is null');
            $this->assertTrue($fJeanPaul->getUpdatedAt() < Carbon::now());
        }

        $entityManager->flush();
    }

    public function testNormalizeFreelance(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $freelanceConsolider = static::getContainer()->get(FreelanceConsolider::class);

        $freelances = $entityManager->getRepository(Freelance::class)->findAll();
        foreach ($freelances as $freelance) {
            $freelanceNormalized = $freelanceConsolider->consolidate($freelance);

            $this->assertNotNull($freelanceNormalized, 'FreelanceNormalized is null');
        }

        $this->assertNotEmpty($freelances, 'No freelances found');
    }

    public function testGetFreelanceDetail(): void
    {
        self::bootKernel();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $freelanceSerializer = static::getContainer()->get(FreelanceSerializer::class);
        $serializer = static::getContainer()->get('serializer');


        $freelanceEntity = $entityManager->getRepository(Freelance::class)->findOneBy([]);

        $freelanceJson = $freelanceSerializer->serializeFreelance($freelanceEntity, ['freelance_detail']);
        $this->assertNotEmpty($freelanceJson, '$freelanceJson is empty');

        $freelanceDeserialized = $serializer->deserialize($freelanceJson, Freelance::class, 'json');
        $this->assertNotNull($freelanceDeserialized, '$freelanceDeserialized is null');
    }

    public function testElasticSearchBasicSearch(): void
    {
        self::bootKernel();

        $freelanceSearchService = static::getContainer()->get(FreelanceSearchService::class);

        $freelances = $freelanceSearchService->searchFreelance('*');
        $this->assertNotEmpty($freelances, 'No freelances found');
    }

    public function testElasticSearchBasicSearchWithSerializer(): void
    {
        self::bootKernel();

        $freelanceSearchService = static::getContainer()->get(FreelanceSearchService::class);
        $freelanceSerializer = static::getContainer()->get(FreelanceSerializer::class);

        $freelances = $freelanceSearchService->searchFreelance('*');
        $this->assertNotEmpty($freelances, 'No freelances found');

        $freelancesJson = $freelanceSerializer->serializeFreelancesConso($freelances, ['freelance_conso']);
        $this->assertNotEmpty($freelancesJson, 'No freelances json found');

        $freelanceArray = json_decode($freelancesJson, true);

        foreach ($freelanceArray as $freelance) {
            self::assertArrayHasKey('id', $freelance);
            self::assertArrayHasKey('firstName', $freelance);
            self::assertArrayHasKey('lastName', $freelance);
            self::assertArrayHasKey('jobTitle', $freelance);
        }
    }

    public function testConnector(): void
    {
        self::bootKernel();

        $freelanceManager = static::getContainer()->get(FreelanceManager::class);


        $this->assertTrue($freelanceManager->getNumberOfFreelancesInJeanMichelWebsiteHomePage() > 0, 'Method is empty');
    }

    public function testFindFirstName(): void
    {
        self::bootKernel();

        $freelanceManager = static::getContainer()->get(FreelanceManager::class);


        $this->assertTrue(!empty($freelanceManager->findTheMostUseFirstname()), 'Method is empty');
    }
}
