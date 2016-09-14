<?php

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    /**
     * @dataProvider getTests
     */
    public function testIssueComment($eventHeader, $payloadFilename, $expectedResponse)
    {
        $client = $this->createClient();
        $client->enableProfiler();
        $errorsMessage = null;

        $body = file_get_contents(__DIR__.'/../webhook_examples/'.$payloadFilename);
        $client->request('POST', '/webhooks/github', [], [], ['HTTP_X-Github-Event' => $eventHeader], $body);
        $response = $client->getResponse();

        if ($profile = $client->getProfile()) {
            $errorsMessage = $this->handleExceptionFromCollector($profile);
        }
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->getStatusCode(), $errorsMessage);

        // a weak sanity check that we went down "the right path" in the controller
        $this->assertEquals($expectedResponse, $responseData);
    }

    public function getTests()
    {
        $tests = [];
        $tests[] = [
            'issue_comment',
            'issue_comment.created.json',
            [
                [
                    'event' => 'issue_comment_created',
                    'action' => 'add labels if required',
                ],
            ],
        ];
        $tests[] = [
            'pull_request',
            'pull_request.opened.json',
            [
                [
                    'event' => 'pr_opened',
                    'action' => 'table description checked',
                ],
                [
                    'event' => 'pr_opened',
                    'action' => 'labels initialized',
                ],
                [
                    'event' => 'pr_opened',
                    'action' => 'user welcomed',
                ],
            ],
        ];
        $tests[] = [
            'issues',
            'issues.labeled.bug.json',
            [
                [
                    'event' => 'issue_event_labeled',
                    'action' => 'added required labels',
                ],
            ],
        ];
        $tests[] = [
            'issues',
            'issues.labeled.feature.json',
            [
                [
                    'event' => 'issue_event_labeled',
                    'action' => 'ignored',
                ],
            ],
        ];
        $tests[] = [
            'pull_request',
            'wrong_repository.pull_request.json',
            [],
        ];

        return $tests;
    }

    private function handleExceptionFromCollector($profile)
    {
        $exception = $profile->getCollector('exception');
        $trace = current($exception->getTrace());

        return $exception->getMessage()
            .' in '.$trace['file']
            .'(line '.$trace['line'].')'
        ;
    }
}
