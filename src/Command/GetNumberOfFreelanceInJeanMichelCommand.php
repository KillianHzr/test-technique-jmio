<?php

namespace App\Command;

use App\Service\FreelanceManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stats:freelances',
    description: 'Get information about jean-michel.io',
)]
class GetNumberOfFreelanceInJeanMichelCommand extends Command
{
    public function __construct(private readonly FreelanceManager $freelanceManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln($this->freelanceManager->getNumberOfFreelancesInJeanMichelWebsiteHomePage() . " freelances");
        return Command::SUCCESS;
    }
}
