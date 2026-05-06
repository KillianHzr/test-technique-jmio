<?php

namespace App\MessageHandler;

use App\Message\InsertFreelanceLinkedInMessage;
use App\Service\FreelanceConsolider;
use App\Service\InsertFreelanceLinkedIn;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class InsertFreelanceLinkedInMessageHandler
{
    public function __construct(
        private InsertFreelanceLinkedIn $insertFreelanceLinkedIn,
        private FreelanceConsolider     $freelanceConsolider,
        private LockFactory             $lockFactory,
        private EntityManagerInterface  $entityManager)
    {
    }

    public function __invoke(InsertFreelanceLinkedInMessage $message): void
    {
        $lock = $this->lockFactory->createLock('insert_freelance', 300, false);

        $lock->acquire(true);
        $freelanceLinkedIn = $this->insertFreelanceLinkedIn->insertFreelanceLinkedIn($message->dto);
        $this->entityManager->flush();

        if ($freelanceLinkedIn->getFreelance()) {
            $this->freelanceConsolider->consolidate($freelanceLinkedIn->getFreelance());
        }
    }
}
