<?php
namespace App\Command;

use App\Entity\Freelance;
use App\Service\FreelanceConsolider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:freelance:enrich',
    description: 'Enrich all freelances with skills and bio',
)]
class EnrichFreelanceCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FreelanceConsolider $freelanceConsolider,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $freelances = $this->entityManager->getRepository(Freelance::class)->findAll();

        $io->progressStart(count($freelances));

        foreach ($freelances as $freelance) {
            $this->freelanceConsolider->consolidate($freelance);
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success('All freelances have been enriched with skills and bio.');

        return Command::SUCCESS;
    }
}
