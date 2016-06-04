<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    /**
     * @dataProvider getTests
     */
    public function testIssueComment($eventHeader, $payloadFilename, $expectedResponse)
    {
        $client = $this->createClient();
        $body = file_get_contents(__DIR__.'/../webhook_examples/'.$payloadFilename);
        $client->request('POST', '/webhooks/github', array(), array(), array('HTTP_X-Github-Event' => $eventHeader), $body);
        $response = $client->getResponse();

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode());

        // a weak sanity check that we went down "the right path" in the controller
        $this->assertEquals($expectedResponse, $responseData);
    }

    public function getTests()
    {
        $tests = array();
        $tests[] = array(
            'issue_comment',
            'issue_comment.created.json',
            array(
                array(
                    'event' => 'issue_comment_created',
                    'action' => 'add labels if required',
                )
            ),
        );
        $tests[] = array(
            'pull_request',
            'pull_request.opened.json',
            array(
                array(
                    'event' => 'pr_opened',
                    'action' => 'table description checked',
                ),
                array(
                    "event" => "pr_opened",
                    "action" => "labels initialized",
                )
            ),
        );
        $tests[] = array(
            'issues',
            'issues.labeled.bug.json',
            array(
                array(
                    'event' => 'issue_event_labeled',
                    'action' => 'added required labels',
                )
            ),
        );
        $tests[] = array(
            'issues',
            'issues.labeled.feature.json',
            array(
                array(
                    'event' => 'issue_event_labeled',
                    'action' => 'ignored',
                )
            ),
        );

        return $tests;
    }
}
