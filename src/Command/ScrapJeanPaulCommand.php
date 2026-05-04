<?php

namespace App\Command;

use App\Dto\FreelanceJeanPaulDto;
use App\Message\InsertFreelanceJeanPaulMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:scrap:jean-paul',
    description: 'Scrap data from JeanPaul',
)]
class ScrapJeanPaulCommand extends Command
{
    public function __construct(private readonly SerializerInterface $serializer, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $jsonData = file_get_contents('./datas/jean-paul.json');

        $jeanPaulDtos = $this->serializer->deserialize($jsonData, FreelanceJeanPaulDto::class . '[]', 'json');

        /** @var FreelanceJeanPaulDto $jeanPaulDto */
        foreach ($jeanPaulDtos as $jeanPaulDto) {
            $this->bus->dispatch(new InsertFreelanceJeanPaulMessage($jeanPaulDto));
        }

        return Command::SUCCESS;
    }
}
