<?php

namespace App\Command;

use App\Dto\FreelanceJeanPaulDto;
use App\Dto\FreelanceLinkedInDto;
use App\Message\InsertFreelanceLinkedInMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:scrap:linkedin',
    description: 'Scrap data from LinkedIn',
)]
class ScrapLinkedInCommand extends Command
{
    public function __construct(private readonly SerializerInterface $serializer, private readonly MessageBusInterface $bus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $jsonData = file_get_contents('./datas/jean-paul.json');

        $linkedInDtos = $this->serializer->deserialize($jsonData, FreelanceJeanPaulDto::class . '[]', 'json');

        /** @var FreelanceLinkedInDto $linkedInDto */
        foreach ($linkedInDtos as $linkedInDto) {
            $io->writeln("Dispatching message for $linkedInDto->url");
            $this->bus->dispatch(new InsertFreelanceLinkedInMessage($linkedInDto));
        }

        return Command::SUCCESS;
    }
}
