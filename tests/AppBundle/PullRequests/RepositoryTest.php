<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\Repository;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    const REPOSITORY_USERNAME = 'loveOSS';
    const REPOSITORY_NAME = 'test';

    private $pullRequestMock;
    private $repository;

    public function setUp()
    {
        $searchMock = $this->createMock('AppBundle\Search\Repository');
        $commentsApiMock = $this->createMock('Github\Api\Issue\Comments');

        $searchMock->method('getPullRequests')
            ->will($this->returnCallback([$this, 'generateExpectedArray']))
        ;

        $commentsApiMock->method('all')
            ->will($this->returnCallback([$this, 'exportCommentsJson']))
        ;

        $this->pullRequestMock = $this->createMock('Lpdigital\Github\Entity\PullRequest');

        $this->pullRequestMock
            ->method('getNumber')
            ->willReturn('123')
        ;

        $this->repository = new Repository(
            $searchMock,
            $commentsApiMock,
            self::REPOSITORY_USERNAME,
            self::REPOSITORY_NAME
        );
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

    public function exportCommentsJson()
    {
        $fileContent = file_get_contents(__DIR__.'/../webhook_examples/pull_request_comments.json');

        return json_decode($fileContent, true);
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

    public function testGetComments()
    {
        $comments = $this->repository->getComments($this->pullRequestMock);
        $this->assertInternalType('array', $comments);
        $firstComment = $comments[0];
        $this->assertInstanceOf('Lpdigital\Github\Entity\Comment', $firstComment);
        $this->assertEquals('mickaelandrieu', $firstComment->getUserLogin());
    }

    private function minimalTests($pullRequests)
    {
        $this->assertInternalType('array', $pullRequests);
        $this->assertInstanceOf('Lpdigital\Github\Entity\PullRequest', $pullRequests[0]);
    }
}
