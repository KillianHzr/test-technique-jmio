<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SearchFreelanceConsoDto
{
    public function __construct(
        public string $query = '*',
        #[Assert\Range(min: 1)]
        public int $page = 1,
        #[Assert\Range(min: 1, max: 100)]
        public int $limit = 10,
        public ?string $sort = null,
        public array $skills = []
    ) {}
}
