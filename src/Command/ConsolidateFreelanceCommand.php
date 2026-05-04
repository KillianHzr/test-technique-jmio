<?php

namespace App\Command;

use App\Entity\Freelance;
use App\Service\FreelanceConsolider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:consolidate:freelance',
    description: 'Normalize freelance',
)]
class ConsolidateFreelanceCommand extends Command
{
    public function __construct(private readonly FreelanceConsolider $freelanceConsolider, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $freelances = $this->entityManager->getRepository(Freelance::class)->findAll();
        /** @var Freelance $freelance */
        foreach ($freelances as $freelance) {
            $this->freelanceConsolider->consolidate($freelance);
        }

        return Command::SUCCESS;
    }
}
