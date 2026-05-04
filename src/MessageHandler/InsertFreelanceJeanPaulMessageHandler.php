<?php

namespace App\MessageHandler;

use App\Message\InsertFreelanceJeanPaulMessage;
use App\Service\InsertFreelanceJeanPaul;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class InsertFreelanceJeanPaulMessageHandler
{
    public function __construct(
        private InsertFreelanceJeanPaul $insertFreelanceJeanPaul,
        private LockFactory             $lockFactory,
        private EntityManagerInterface  $entityManager)
    {
    }

    public function __invoke(InsertFreelanceJeanPaulMessage $message): void
    {
        die('debug');
        $lock = $this->lockFactory->createLock('insert_freelance', 300, false);

        $lock->acquire(true);
        $this->insertFreelanceJeanPaul->insertFreelanceJeanPaul($message->dto);
        $this->entityManager->flush();
    }
}
