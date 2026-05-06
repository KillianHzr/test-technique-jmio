<?php
namespace App\Service;

use App\Dto\SearchResult;
use Elastica\Query;
use Elastica\Query\BoolQuery;
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

    public function searchFreelance(string $query, int $page = 1, int $limit = 10, ?string $sort = null): SearchResult
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

        return new SearchResult(
            results: $results,
            total: $paginator->getNbResults(),
            pages: (int) ceil($paginator->getNbResults() / $limit),
            currentPage: $page,
            limit: $limit
        );
    }

    private function buildQuery(string $query): Query
    {
        if ($query === '*') {
            return new Query(new MatchAll());
        }

        $fuzzy = new MultiMatch();
        $fuzzy->setQuery($query);
        $fuzzy->setFields(['fullName^3', 'firstName^2', 'lastName^2', 'jobTitle^2', 'linkedInUrl']);
        $fuzzy->setFuzziness('AUTO');
        $fuzzy->setOperator(MultiMatch::OPERATOR_OR);

        $ngram = new MultiMatch();
        $ngram->setQuery($query);
        $ngram->setFields(['fullName.ngram^3', 'firstName.ngram^2', 'lastName.ngram^2', 'jobTitle.ngram']);
        $ngram->setOperator(MultiMatch::OPERATOR_OR);

        $bool = new BoolQuery();
        $bool->addShould($fuzzy);
        $bool->addShould($ngram);
        $bool->setMinimumShouldMatch(1);

        return new Query($bool);
    }
}
