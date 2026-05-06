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

    public function searchFreelance(string $query, int $page = 1, int $limit = 10, ?string $sort = null): array
    {
        $elasticaQuery = $this->buildQuery($query);

        if ($sort) {
            if ($sort === 'name_asc') {
                $elasticaQuery->setSort(['lastName.keyword' => 'asc', 'firstName.keyword' => 'asc']);
            } elseif ($sort === 'name_desc') {
                $elasticaQuery->setSort(['lastName.keyword' => 'desc', 'firstName.keyword' => 'desc']);
            }
        }

        $paginator = $this->freelanceFinder->findPaginated($elasticaQuery);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage($limit);

        $results = [];
        foreach ($paginator->getCurrentPageResults() as $item) {
            $results[] = $item;
        }

        return [
            'results' => $results,
            'total'   => $paginator->getNbResults(),
            'pages'   => (int) ceil($paginator->getNbResults() / $limit),
        ];
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
