<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\Labels;
use AppBundle\PullRequests\LabelNotFoundException;
use AppBundle\PullRequests\Reporter;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class ReporterTest extends \PHPUnit_Framework_TestCase
{
    private $reporter;

    private $repositoryMock;

    public function setUp()
    {
        $this->repositoryMock = $this->createMock('AppBundle\PullRequests\Repository');

        $this->repositoryMock->method('findAllWithLabel')
            ->willReturn($this->createPullRequestsArray())
        ;

        $this->reporter = new Reporter($this->repositoryMock);
    }

    public function testReportActivityForLabel()
    {
        $base = 'develop';
        $label = Labels::WAITING_FOR_CODE_REVIEW;

        $this->repositoryMock->expects($this->once())
            ->method('findAllWithLabel')
            ->with($label, $base)
        ;

        $this->reporter->reportActivityForLabel($base, $label);
    }

    public function testReportActivityForLabelWithInvalidLabel()
    {
        $base = 'develop';
        $label = 'invalid-label';

        $this->repositoryMock->expects($this->never())
            ->method('findAllWithLabel')
            ->with($label, $base)
        ;

        $this->expectException(LabelNotFoundException::class);

        $this->reporter->reportActivityForLabel($base, $label);
    }

    private function createPullRequestsArray()
    {
        return [
            $this->createMock('Lpdigital\Github\Entity\PullRequest'),
            $this->createMock('Lpdigital\Github\Entity\PullRequest'),
        ];
    }
}
