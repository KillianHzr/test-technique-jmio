<?php

namespace App\Command;

use App\Service\FreelanceSearchService;
use App\Service\FreelanceSerializer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'app:freelance:search',
    description: 'Search freelances',
)]
class FreelanceSearchCommand extends Command
{
    public function __construct(
        private readonly FreelanceSearchService $freelanceSearchService,
        private readonly FreelanceSerializer $freelanceSerializer
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'Search query')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = $input->getArgument('query');

        $freelances = $this->freelanceSearchService->searchFreelance($query);

        $freelanceJson = $this->freelanceSerializer->serializeFreelancesConso($freelances,  ['freelance_conso']);

        dump($freelanceJson);
        return Command::SUCCESS;
    }
}
