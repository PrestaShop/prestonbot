<?php

namespace Tests\AppBundle\Search;

use AppBundle\Search\Repository;
use Github\Api\GraphQL;
use Github\Api\Search;
use PHPUnit\Framework\TestCase;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class RepositoryTest extends TestCase
{
    private $repository;
    private $searchApiMock;
    private $graphQL;

    public function setUp()
    {
        $this->searchApiMock = $this->createMock(Search::class);
        $this->graphQL = $this->createMock(GraphQL::class);

        $this->repository = new Repository($this->searchApiMock, $this->graphQL, 'fakeUsername', 'fakeName');
    }

    public function tearDown()
    {
        $this->repository = null;
        $this->searchApiMock = null;
        $this->graphQL = null;
    }

    /**
     * @dataProvider filters
     *
     * @param mixed $filters
     */
    public function testGetAllPullRequests($filters)
    {
        $this->searchApiMock
            ->expects($this->once())
            ->method('issues')
            ->with($this->buildQuery($filters))
            ->willReturn([
                'countable' => 0,
                'incomplete_results' => true,
                'items' => [],
            ])
        ;
        $response = $this->repository->getPullRequests($filters);
    }

    public function filters()
    {
        return [
            'no-filter,no-branch' => [[]],
            'no-filter,branch' => [['base' => 'master']],
            'filter,branch' => [['base' => 'foo', 'label' => '"wip"']],
        ];
    }

    private function buildQuery($filters)
    {
        $basicFilters = [
            'type' => 'pr',
            'state' => 'open',
            'repo' => 'fakeUsername/fakeName',
        ];

        $allFilters = array_merge($basicFilters, $filters);

        $query = '';
        foreach ($allFilters as $filter => $filterValue) {
            $query .= ' '.$filter.':'.$filterValue;
        }

        return $query;
    }
}
