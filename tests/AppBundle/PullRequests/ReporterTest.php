<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\PullRequests\LabelNotFoundException;
use AppBundle\PullRequests\Labels;
use AppBundle\PullRequests\Reporter;
use PHPUnit\Framework\TestCase;
use PrestaShop\Github\Entity\PullRequest;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class ReporterTest extends TestCase
{
    private $reporter;

    private $repositoryMock;

    public function setUp(): void
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
            $this->createMock(PullRequest::class),
            $this->createMock(PullRequest::class),
        ];
    }
}
