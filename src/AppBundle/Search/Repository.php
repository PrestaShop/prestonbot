<?php

namespace AppBundle\Search;

use Github\Api\Search;

/**
 * Access the Search API.
 *
 * @doc https://github.com/KnpLabs/php-github-api/blob/master/doc/search.md
 */
class Repository
{
    /**
     * @var Search
     */
    private $searchApi;
    /**
     * @var string
     */
    private $repositoryUsername;
    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(Search $searchApi, string $repositoryUsername, string $repositoryName)
    {
        $this->searchApi = $searchApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    public function getPullRequests($filters = [])
    {
        $basicFilters = [
            'type' => 'pr',
            'state' => 'open',
            'repo' => $this->repositoryUsername.'/'.$this->repositoryName,
        ];

        $allFilters = array_merge($basicFilters, $filters);

        return $this->searchApi->issues($this->buildQuery($allFilters));
    }

    /**
     * @param $filters
     *
     * @return string
     */
    private function buildQuery($filters)
    {
        $query = '';
        foreach ($filters as $filter => $filterValue) {
            $query .= ' '.$filter.':'.$filterValue;
        }

        return $query;
    }
}
