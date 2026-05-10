<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-demo-user',
    description: 'Create a demo recruiter user',
)]
class CreateDemoUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = 'recruteur@jean-michel.io';
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_RECRUITER']);
            $user->setCompanyName('Jean-Michel Corp');
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $output->writeln('Demo user created: ' . $email);
        } else {
            $output->writeln('Demo user already exists.');
        }

        return Command::SUCCESS;
    }
}
