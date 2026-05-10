<?php

namespace App\Tests;

use App\Entity\FreelanceConso;
use App\Entity\Selection;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RecruiterSpaceTest extends KernelTestCase
{
    public function testUserCreationAndAuthentication(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $email = 'test-recruiter@example.com';
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($user) {
            $em->remove($user);
            $em->flush();
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_RECRUITER']);
        $user->setPassword($hasher->hashPassword($user, 'password'));
        
        $em->persist($user);
        $em->flush();

        $this->assertNotNull($user->getId());
        $this->assertContains('ROLE_RECRUITER', $user->getRoles());
        $this->assertTrue($hasher->isPasswordValid($user, 'password'));
    }

    public function testSelectionManagement(): void
    {
        self::bootKernel();
        $em = static::getContainer()->get('doctrine')->getManager();

        $recruiter = $em->getRepository(User::class)->findOneBy(['email' => 'test-recruiter@example.com']);
        $freelance = $em->getRepository(FreelanceConso::class)->findOneBy([]);

        $this->assertNotNull($recruiter, 'Run testUserCreationAndAuthentication first');
        $this->assertNotNull($freelance, 'Database must be populated');

        $selection = new Selection();
        $selection->setName('Test Selection');
        $selection->setRecruiter($recruiter);
        $selection->addFreelance($freelance);

        $em->persist($selection);
        $em->flush();

        $this->assertNotNull($selection->getId());
        $this->assertEquals('Test Selection', $selection->getName());
        $this->assertCount(1, $selection->getFreelances());
        $this->assertEquals($freelance->getId(), $selection->getFreelances()->first()->getId());
    }
}
