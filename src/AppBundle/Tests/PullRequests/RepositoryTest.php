<?php

namespace AppBundle\Tests\PullRequests;

use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\Repository;
use Github\Api\Issue;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $repository;
    
    public function setUp()
    {
        $issueApiMock = $this->getMockBuilder(Issue::class)
            ->disableOriginalConstructor()
            ->setMethods(['all'])
            ->getMock()
        ;
        
        /* the mock will return response from fixtures */
        $issueApiMock->method('all')
            ->will($this->returnCallback([$this, 'generateExpectedGitHubResponse']))
        ;
        
        $this->repository = new Repository($issueApiMock, 'fakeUsername', 'fakeName');
    }
    
    public function tearDown()
    {
        $this->repository = null;
    }
    
    public function generateExpectedGitHubResponse($repositoryUsername, $repositoryName, $args)
    {
        if ([] === $args) {
            $filename = 'all_prs.json';
        } elseif (isset($args['labels']) && 'bug' === $args['labels']) {
            $filename = 'one_label_prs.json';
        } elseif (isset($args['labels']) && 'bug,question' === $args['labels']) {
            $filename = 'labels_prs.json';
        } else {
            $filename = 'waiting_prs.json'; // not implemented yet
        }
        
        $fileContent = file_get_contents(__DIR__.'/../webhook_examples/'.$filename);
        
        return json_decode($fileContent, true);
    }
    
    public function testFindAll()
    {
        /* 8 pull requests expected */
        $pullRequests = $this->repository->findAll();
        $this->makeMinimalTests($pullRequests);
        $this->assertCount(8, $pullRequests, 'Repository:findAll() should return 8 pull requests.');
    }
    
    public function testFindAllWithTag()
    {
        /* only one pull request labelized with `bug` entry */
        $pullRequests = $this->repository->findAllWithTag('bug');
        $this->makeMinimalTests($pullRequests);
        $this->assertCount(1, $pullRequests, 'There is only 1 pull request with `bug` label.');
    }
    
    public function testFindAllWithTags()
    {
        /* then, 2 pull requests with both `bug` and `question` labels */
        $pullRequests = $this->repository->findAllWithTags(['bug', 'question']);
        $this->makeMinimalTests($pullRequests);
        $this->assertCount(2, $pullRequests, 'There are 3 pull requests with both `bug` and `question` labels.');
    }
    
    private function makeMinimalTests($pullRequests)
    {
        $this->assertInternalType('array', $pullRequests, 'The repository is expected to return an array.');
        $this->assertInstanceOf(PullRequest::class, $pullRequests[0], 'And it is an array of PullRequest objects.');
    }
}
