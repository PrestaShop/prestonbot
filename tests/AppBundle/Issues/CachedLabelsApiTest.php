<?php

namespace tests\AppBundle\Issues;

use AppBundle\Issues\CachedLabelsApi;
use Github\Api\Issue\Labels;
use PHPUnit\Framework\TestCase;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CachedLabelsApiTest extends TestCase
{
    const USER_NAME = 'weaverryan';

    const REPO_NAME = 'carson';

    /**
     * @var Labels|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendApi;

    /**
     * @var CachedLabelsApi
     */
    private $api;

    protected function setUp()
    {
        $this->backendApi = $this->getMockBuilder('Github\Api\Issue\Labels')
            ->disableOriginalConstructor()
            ->getMock();
        $this->api = new CachedLabelsApi($this->backendApi, self::USER_NAME, self::REPO_NAME);
    }

    public function testGetIssueLabels()
    {
        $this->backendApi->expects($this->once())
            ->method('all')
            ->with(self::USER_NAME, self::REPO_NAME, 1234)
            ->willReturn([
                ['name' => 'a'],
                ['name' => 'b'],
                ['name' => 'c'],
            ]);

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));

        // Subsequent access goes to cache
        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));
    }

    public function testAddIssueLabel()
    {
        $this->backendApi->expects($this->never())
            ->method('all');

        $this->backendApi->expects($this->once())
            ->method('add')
            ->with(self::USER_NAME, self::REPO_NAME, 1234, 'a');

        $this->api->addIssueLabel(1234, 'a');
    }

    public function testAddIssueLabelUpdatesCache()
    {
        $this->backendApi->expects($this->once())
            ->method('all')
            ->with(self::USER_NAME, self::REPO_NAME, 1234)
            ->willReturn([
                ['name' => 'a'],
                ['name' => 'b'],
                ['name' => 'c'],
            ]);

        $this->backendApi->expects($this->once())
            ->method('add')
            ->with(self::USER_NAME, self::REPO_NAME, 1234, 'd');

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));

        $this->api->addIssueLabel(1234, 'd');

        $this->assertSame(['a', 'b', 'c', 'd'], $this->api->getIssueLabels(1234));
    }

    public function testAddIssueLabelIgnoresDuplicate()
    {
        $this->backendApi->expects($this->once())
            ->method('all')
            ->with(self::USER_NAME, self::REPO_NAME, 1234)
            ->willReturn([
                ['name' => 'a'],
                ['name' => 'b'],
                ['name' => 'c'],
            ]);

        $this->backendApi->expects($this->never())
            ->method('add');

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));

        $this->api->addIssueLabel(1234, 'c');

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));
    }

    public function testRemoveIssueLabel()
    {
        $this->backendApi->expects($this->never())
            ->method('all');

        $this->backendApi->expects($this->once())
            ->method('remove')
            ->with(self::USER_NAME, self::REPO_NAME, 1234, 'a');

        $this->api->removeIssueLabel(1234, 'a');
    }

    public function testRemoveIssueLabelUpdatesCache()
    {
        $this->backendApi->expects($this->once())
            ->method('all')
            ->with(self::USER_NAME, self::REPO_NAME, 1234)
            ->willReturn([
                ['name' => 'a'],
                ['name' => 'b'],
                ['name' => 'c'],
            ]);

        $this->backendApi->expects($this->once())
            ->method('remove')
            ->with(self::USER_NAME, self::REPO_NAME, 1234, 'a');

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));

        $this->api->removeIssueLabel(1234, 'a');

        $this->assertSame(['b', 'c'], $this->api->getIssueLabels(1234));
    }

    public function testRemoveIssueLabelIgnoresUnsetLabel()
    {
        $this->backendApi->expects($this->once())
            ->method('all')
            ->with(self::USER_NAME, self::REPO_NAME, 1234)
            ->willReturn([
                ['name' => 'a'],
                ['name' => 'b'],
                ['name' => 'c'],
            ]);

        $this->backendApi->expects($this->never())
            ->method('remove');

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));

        $this->api->removeIssueLabel(1234, 'd');

        $this->assertSame(['a', 'b', 'c'], $this->api->getIssueLabels(1234));
    }
}
