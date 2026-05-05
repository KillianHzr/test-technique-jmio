<?php
namespace App\Service;

use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Query\MultiMatch;
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

    public function searchFreelance(string $query, int $page = 1, int $limit = 10): array
    {
        $elasticaQuery = $this->buildQuery($query);
        $elasticaQuery->setFrom(($page - 1) * $limit);
        $elasticaQuery->setSize($limit);

        return $this->freelanceFinder->find($elasticaQuery);
    }

    private function buildQuery(string $query): Query
    {
        if ($query === '*') {
            return new Query(new MatchAll());
        }

        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($query);
        $multiMatch->setFields(['fullName^3', 'firstName^2', 'lastName^2', 'jobTitle^2', 'linkedInUrl']);
        $multiMatch->setFuzziness('AUTO');
        $multiMatch->setOperator(MultiMatch::OPERATOR_OR);

        return new Query($multiMatch);
    }
}
