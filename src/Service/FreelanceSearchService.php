<?php
namespace App\Service;

use App\Dto\SearchResult;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchAll;
use Elastica\Query\MultiMatch;
use Elastica\Query\Prefix;
use Elastica\Query\MoreLikeThis;
use Elastica\Aggregation\Terms;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class FreelanceSearchService
{
    public function __construct(
        #[Autowire(service: "fos_elastica.finder.freelance")]
        private PaginatedFinderInterface $freelanceFinder,
        #[Autowire(service: "fos_elastica.index.freelance")]
        private Index $freelanceIndex,
    )
    {
    }

    public function searchFreelance(string $query, int $page = 1, int $limit = 10, ?string $sort = null, array $skills = []): SearchResult
    {
        $elasticaQuery = $this->buildQuery($query, $skills);

        $agg = new Terms('skills_facets');
        $agg->setField('skills.keyword');
        $agg->setSize(100);
        $elasticaQuery->addAggregation($agg);

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

        $adapter = $paginator->getAdapter();
        $searchResponse = $this->freelanceIndex->search($elasticaQuery);
        $buckets = $searchResponse->getAggregation('skills_facets')['buckets'] ?? [];
        
        $facets = [];
        foreach ($buckets as $bucket) {
            $facets[$bucket['key']] = $bucket['doc_count'];
        }

        return new SearchResult(
            results: $results,
            total: $paginator->getNbResults(),
            pages: (int) ceil($paginator->getNbResults() / $limit),
            currentPage: $page,
            limit: $limit,
            aggregations: $facets
        );
    }

    public function getAutocompleteSuggestion(string $query): string
    {
        $boolQuery = new BoolQuery();
        
        $prefixJob = new Prefix(['jobTitle' => strtolower($query)]);
        $prefixFirst = new Prefix(['firstName' => strtolower($query)]);
        $prefixLast = new Prefix(['lastName' => strtolower($query)]);
        
        $boolQuery->addShould($prefixJob);
        $boolQuery->addShould($prefixFirst);
        $boolQuery->addShould($prefixLast);
        $boolQuery->setMinimumShouldMatch(1);

        $elasticaQuery = new Query($boolQuery);
        $elasticaQuery->setSize(1);

        $results = $this->freelanceFinder->find($elasticaQuery);

        if (empty($results)) {
            return '';
        }

        /** @var \App\Entity\FreelanceConso $freelance */
        $freelance = $results[0];

        $firstName = $freelance->getFirstName();
        $lastName  = $freelance->getLastName();
        $jobTitle  = $freelance->getJobTitle();
        $fullName  = trim(($firstName ?? '') . ' ' . ($lastName ?? ''));

        if ($jobTitle && str_starts_with(strtolower($jobTitle), strtolower($query))) {
            return $jobTitle;
        }

        if ($fullName && str_starts_with(strtolower($fullName), strtolower($query))) {
            return $fullName;
        }

        if ($firstName && str_starts_with(strtolower($firstName), strtolower($query))) {
            return $fullName;
        }

        $reverseFullName = trim(($lastName ?? '') . ' ' . ($firstName ?? ''));
        if ($lastName && str_starts_with(strtolower($lastName), strtolower($query))) {
            return $reverseFullName;
        }

        return '';
    }

    public function getMatchingFreelances(int $id, int $limit = 3): array
    {
        $mlt = new MoreLikeThis();
        $mlt->setFields(['jobTitle', 'skills']);
        $mlt->setLike(['_id' => (string) $id]);
        $mlt->setMinTermFrequency(1);
        $mlt->setMinDocFrequency(1);

        $query = new Query($mlt);
        $query->setSize($limit);

        return $this->freelanceFinder->find($query);
    }

    public function getAllSkillsWithCounts(): array
    {
        $query = new Query(new MatchAll());
        $agg = new Terms('skills_agg');
        $agg->setField('skills.keyword');
        $agg->setSize(100);

        $query->addAggregation($agg);
        $query->setSize(0);

        $results = $this->freelanceIndex->search($query);
        $buckets = $results->getAggregation('skills_agg')['buckets'] ?? [];

        $skills = [];
        foreach ($buckets as $bucket) {
            $skills[] = [
                'name' => $bucket['key'],
                'count' => $bucket['doc_count']
            ];
        }

        usort($skills, fn($a, $b) => $b['count'] <=> $a['count']);

        return $skills;
    }

    private function buildQuery(string $query, array $skills = []): Query
    {
        if ($query === '*' && empty($skills)) {
            return new Query(new MatchAll());
        }

        $bool = new BoolQuery();

        if ($query !== '*') {
            $queryPart = new BoolQuery();
            
            $fuzzy = new MultiMatch();
            $fuzzy->setQuery($query);
            $fuzzy->setFields(['fullName^3', 'firstName^2', 'lastName^2', 'jobTitle^2', 'linkedInUrl', 'skills^2']);
            $fuzzy->setFuzziness('AUTO');
            $fuzzy->setOperator(MultiMatch::OPERATOR_AND);

            $ngram = new MultiMatch();
            $ngram->setQuery($query);
            $ngram->setFields(['fullName.ngram^3', 'firstName.ngram^2', 'lastName.ngram^2', 'jobTitle.ngram', 'skills.ngram']);
            $ngram->setOperator(MultiMatch::OPERATOR_AND);

            $queryPart->addShould($fuzzy);
            $queryPart->addShould($ngram);
            $queryPart->setMinimumShouldMatch(1);
            
            $bool->addMust($queryPart);
        }

        if (!empty($skills)) {
            $skillsPart = new BoolQuery();
            foreach ($skills as $skill) {
                $term = new Query\Term(['skills.keyword' => $skill]);
                $skillsPart->addShould($term);
            }
            $skillsPart->setMinimumShouldMatch(1);
            $bool->addMust($skillsPart);
        }

        return new Query($bool);
    }
}
