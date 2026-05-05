<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class FreelanceLinkedInDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\NotNull]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\NotNull]
        public string $lastName,
        public ?string $jobTitle = null,
        #[Assert\NotBlank]
        #[Assert\NotNull]
        public string $url
    )
    {
    }
}