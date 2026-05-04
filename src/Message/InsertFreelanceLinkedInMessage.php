<?php

namespace App\Message;

use App\Dto\FreelanceLinkedInDto;

final class InsertFreelanceLinkedInMessage
{
     public function __construct(
         public FreelanceLinkedInDto $dto
     ) {
     }
}
