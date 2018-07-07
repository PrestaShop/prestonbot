<?php

namespace Tests\AppBundle\Issues;

use AppBundle\Issues\CachedLabelsApi;
use AppBundle\Issues\Status;
use AppBundle\Issues\StatusApi;
use PHPUnit\Framework\TestCase;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StatusApiTest extends TestCase
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

    public function testAddIssueLabel()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review']);

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Code reviewed');

        $this->api->addIssueLabel(1234, Status::CODE_REVIEWED);
    }

    public function testAddIssueLabelWithBugAlias()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review']);

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Bug');

        $this->api->addIssueLabel(1234, 'bug fix');
    }

    public function testAddIssueLabelWithFeatureAlias()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Feature', 'Status: Needs Review']);

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Feature');

        $this->api->addIssueLabel(1234, 'new feature');
    }

    public function testAddIssueLabelWithImprovementAlias()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Improvement', 'Status: Needs Review']);

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Improvement');

        $this->api->addIssueLabel(1234, 'improvement');
    }

    public function testGetNeedsReviewUrl()
    {
        $this->assertSame(
            'https://github.com/weaverryan/carson/labels/waiting%20for%20code%20review',
            $this->api->getNeedsReviewUrl()
        );
    }
}
