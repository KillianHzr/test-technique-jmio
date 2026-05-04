<?php

namespace App\Message;

use App\Dto\FreelanceJeanPaulDto;

final class InsertFreelanceJeanPaulMessage
{
     public function __construct(
         public FreelanceJeanPaulDto $dto
     ) {
     }
}
