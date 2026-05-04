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
        #[Assert\NotBlank]
        #[Assert\NotNull]
        public string $jobTitle,
        #[Assert\NotBlank]
        #[Assert\NotNull]
        public string $url
    )
    {
    }
}