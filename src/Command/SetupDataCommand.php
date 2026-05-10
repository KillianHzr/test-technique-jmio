<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:setup:data',
    description: 'Full data initialization: Scraping, Consuming and Indexing',
)]
class SetupDataCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $application = $this->getApplication();
        
        $application->setAutoExit(false);

        $io->title('Starting full data initialization...');

        try {
            $io->section('0. Configuring Messenger transports (RabbitMQ)...');
            $setupCmd = $application->find('messenger:setup-transports');
            $setupCmd->run(new ArrayInput(['--no-interaction' => true]), $output);

            $io->section('1. Scraping LinkedIn (Queuing messages)...');
            $linkedInCmd = $application->find('app:scrap:linkedin');
            $linkedInCmd->run(new ArrayInput([]), $output);

            $io->section('2. Scraping Jean-Paul (Queuing messages)...');
            $jeanPaulCmd = $application->find('app:scrap:jean-paul');
            $jeanPaulCmd->run(new ArrayInput([]), $output);

            $io->section('3. Processing queue (Writing to Database and Consolidation)...');
            $io->note('This step actually saves the freelances to MySQL. Please wait...');

            $linkedInCount = count(json_decode(file_get_contents('./datas/linkedin.json'), true));
            $jeanPaulCount = count(json_decode(file_get_contents('./datas/jean-paul.json'), true));
            $totalMessages = $linkedInCount + $jeanPaulCount;

            $process = new \Symfony\Component\Process\Process([
                'php', 'bin/console', 'messenger:consume', 'insert_async', 
                '--limit=' . $totalMessages, '-vv'
            ]);
            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            $io->section('4. Populating Elasticsearch index...');
            $populateCmd = $application->find('fos:elastica:populate');
            $populateCmd->run(new ArrayInput([
                '--no-interaction' => true
            ]), $output);

            $io->success('Data initialization complete! Your site is ready.');
        } catch (\Exception $e) {
            $io->error('An error occurred during setup: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
