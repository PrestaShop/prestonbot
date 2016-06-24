<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\Repository;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $repository;

    public function setUp()
    {
        $searchMock = $this->createMock('AppBundle\Search\Repository');

        $searchMock->method('getPullRequests')
            ->will($this->returnCallback([$this, 'generateExpectedArray']))
        ;

        $this->repository = new Repository($searchMock);
    }

    public function tearDown()
    {
        $this->repository = null;
    }

    public function generateExpectedArray($filters)
    {
        if (isset($filters['label'])) {
            $filename = 'search_repository_one_label.json';
        } else {
            $filename = 'search_repository_all.json';
        }

        $fileContent = file_get_contents(__DIR__.'/../webhook_examples/'.$filename);

        return [
            'count' => 0,
            'incomplete_results' => false,
            'items' => json_decode($fileContent, true),
        ];
    }

    public function testFindAll()
    {
        $pullRequests = $this->repository->findAll();
        $this->minimalTests($pullRequests);
    }

    public function testFindAllWithLabel()
    {
        $pullRequests = $this->repository->findAllWithLabel('waiting for code review');
        $this->minimalTests($pullRequests);
    }

    public function minimalTests($pullRequests)
    {
        $this->assertInternalType('array', $pullRequests);
        $this->assertInstanceOf('Lpdigital\Github\Entity\PullRequest', $pullRequests[0]);
    }
}
