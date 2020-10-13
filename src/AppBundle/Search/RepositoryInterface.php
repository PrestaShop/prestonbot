<?php

namespace AppBundle\Search;

interface RepositoryInterface
{
    /**
     * @param array $filters
     *
     * @return array
     */
    public function getPullRequests($filters = []): array;

    /**
     * @param $query
     * @param array $variables
     *
     * @return array
     */
    public function graphQL($query, $variables = []): array;
}
