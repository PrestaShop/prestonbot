<?php

namespace Tests\AppBundle\Issues\GitHub;

use AppBundle\Issues\CachedLabelsApi;
use AppBundle\Issues\StatusApi;
use AppBundle\Issues\Status;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StatusApiTest extends \PHPUnit_Framework_TestCase
{
    const USER_NAME = 'weaverryan';

    const REPO_NAME = 'carson';

    /**
     * @var CachedLabelsApi|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelsApi;

    /**
     * @var StatusApi
     */
    private $api;

    protected function setUp()
    {
        $this->labelsApi = $this->getMockBuilder('AppBundle\Issues\CachedLabelsApi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->api = new StatusApi($this->labelsApi, self::USER_NAME, self::REPO_NAME);
    }

    public function testSetIssueStatus()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review']);

        $this->labelsApi->expects($this->once())
            ->method('removeIssueLabel')
            ->with(1234, 'Status: Needs Review');

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Code reviewed');

        $this->api->setIssueStatus(1234, Status::CODE_REVIEWED);
    }

    public function testSetIssueStatusWithoutPreviousStatus()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug']);

        $this->labelsApi->expects($this->never())
            ->method('removeIssueLabel');

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Code reviewed');

        $this->api->setIssueStatus(1234, Status::CODE_REVIEWED);
    }

    public function testSetIssueStatusRemovesExcessStatuses()
    {
        $this->labelsApi->expects($this->at(0))
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(
                [
                    'Bug',
                    'Status: Needs Review',
                    'QA-approved',
                ]
            );

        $this->labelsApi->expects($this->at(1))
            ->method('removeIssueLabel')
            ->with(1234, 'Status: Needs Review');

        $this->labelsApi->expects($this->at(2))
            ->method('removeIssueLabel')
            ->with(1234, 'QA-approved');

        $this->labelsApi->expects($this->at(3))
            ->method('addIssueLabel')
            ->with(1234, 'Code reviewed');

        $this->api->setIssueStatus(1234, Status::CODE_REVIEWED);
    }

    public function testSetIssueStatusDoesNothingIfAlreadySet()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review']);

        $this->labelsApi->expects($this->never())
            ->method('removeIssueLabel');

        $this->labelsApi->expects($this->never())
            ->method('addIssueLabel');

        $this->api->setIssueStatus(1234, Status::NEEDS_REVIEW);
    }

    public function testSetIssueStatusRemovesExcessLabelsIfAlreadySet()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review', 'Code reviewed']);

        $this->labelsApi->expects($this->once())
            ->method('removeIssueLabel')
            ->with(1234, 'Status: Needs Review');

        $this->labelsApi->expects($this->never())
            ->method('addIssueLabel');

        $this->api->setIssueStatus(1234, Status::CODE_REVIEWED);
    }

    public function testSetIssueStatusRemovesUnconfirmedWhenBugIsReviewed()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review', 'Unconfirmed']);

        $this->labelsApi->expects($this->at(1))
            ->method('removeIssueLabel')
            ->with(1234, 'Status: Needs Review');

        $this->labelsApi->expects($this->at(2))
            ->method('removeIssueLabel')
            ->with(1234, 'Unconfirmed');

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Code reviewed');

        $this->api->setIssueStatus(1234, Status::CODE_REVIEWED);
    }

    public function testGetIssueStatus()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review']);

        $this->assertSame(Status::NEEDS_REVIEW, $this->api->getIssueStatus(1234));
    }

    public function testGetIssueStatusReturnsFirst()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review', 'Code reviewed']);

        $this->assertSame(Status::NEEDS_REVIEW, $this->api->getIssueStatus(1234));
    }

    public function testGetIssueStatusReturnsNullIfNoneSet()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug']);

        $this->assertNull($this->api->getIssueStatus(1234));
    }

    public function testGetNeedsReviewUrl()
    {
        $this->assertSame('https://github.com/weaverryan/carson/labels/waiting%20for%20code%20review', $this->api->getNeedsReviewUrl());
    }
}
