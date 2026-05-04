<?php
namespace App\Service;

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


readonly class FreelanceSearchService
{
    public function __construct(
        #[Autowire(service: "fos_elastica.finder.freelance")]
        private PaginatedFinderInterface $freelanceFinder,
    )
    {
    }

    public function searchFreelance(string $query): array
    {
        return $this->freelanceFinder->find($query);
    }
}