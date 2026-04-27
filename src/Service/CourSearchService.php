<?php

namespace App\Service;

use FOS\ElasticaBundle\Finder\TransformedFinder;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use Elastica\Query\MatchAll;

class CourSearchService
{
    public function __construct(
        private TransformedFinder $coursFinder
    ) {}

    public function search(string $search = '', string $niveau = '', string $statut = ''): array
    {
        $boolQuery = new BoolQuery();

        if ($search) {
            $multiMatch = new MultiMatch();
            $multiMatch->setQuery($search);
            $multiMatch->setFields(['titre^3', 'formateur^2', 'description']);
            $multiMatch->setType(MultiMatch::TYPE_BEST_FIELDS);
            $multiMatch->setFuzziness('AUTO');
            $boolQuery->addMust($multiMatch);
        } else {
            $boolQuery->addMust(new MatchAll());
        }

        if ($niveau) {
            $boolQuery->addFilter(new Term(['niveau' => $niveau]));
        }

        if ($statut) {
            $boolQuery->addFilter(new Term(['statut' => strtolower($statut)]));
        }

        $query = new Query($boolQuery);
        $query->setSize(50);

        return $this->coursFinder->find($query);
    }
}