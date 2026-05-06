<?php

namespace App\MessageHandler;

use App\Message\InsertFreelanceJeanPaulMessage;
use App\Service\FreelanceConsolider;
use App\Service\InsertFreelanceJeanPaul;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class InsertFreelanceJeanPaulMessageHandler
{
    public function __construct(
        private InsertFreelanceJeanPaul $insertFreelanceJeanPaul,
        private FreelanceConsolider     $freelanceConsolider,
        private LockFactory             $lockFactory,
        private EntityManagerInterface  $entityManager)
    {
    }

    public function __invoke(InsertFreelanceJeanPaulMessage $message): void
    {
        $lock = $this->lockFactory->createLock('insert_freelance', 300, false);

        $lock->acquire(true);
        $freelanceJeanPaul = $this->insertFreelanceJeanPaul->insertFreelanceJeanPaul($message->dto);
        $this->entityManager->flush();

        if ($freelanceJeanPaul->getFreelance()) {
            $this->freelanceConsolider->consolidate($freelanceJeanPaul->getFreelance());
        }
    }
}
