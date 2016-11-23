<?php

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    /**
     * @dataProvider getTests
     */
    public function testActions($eventHeader, $payloadFilename, $expectedResponse)
    {
        $client = $this->createClient();
        $client->enableProfiler();
        $errorsMessage = null;

        $body = file_get_contents(__DIR__.'/../webhook_examples/'.$payloadFilename);
        $client->request('POST', '/webhooks/github', [], [], ['HTTP_X-Github-Event' => $eventHeader], $body);
        $response = $client->getResponse();

        $errorsMessage = 'OK';
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
        $tests['Issue comments'] = [
            'issue_comment',
            'issue_comment.created.json',
            [
                [
                    'event' => 'issue_comment_created',
                    'action' => 'add labels if required',
                ],
            ],
        ];
        $tests['Pull request creation'] = [
            'pull_request',
            'pull_request.opened.json',
            [
                [
                    'event' => 'pr_opened',
                    'action' => 'user welcomed',
                ],
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
                    'action' => 'checked for new translations',
                    'status' => 'not_found',
                ],
                [
                    'event' => 'pr_opened',
                    'action' => 'commits labels checked',
                    'status' => 'not_valid',
                ],
            ],
        ];
        $tests['Pull request creation with wording'] = [
            'pull_request',
            'pull_request_opened_wording.json',
            [
                [
                    'event' => 'pr_opened',
                    'action' => 'user welcomed',
                ],
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
                    'action' => 'checked for new translations',
                    'status' => 'found',
                ],
                [
                    'event' => 'pr_opened',
                    'action' => 'commits labels checked',
                    'status' => 'valid',
                ],
            ],
        ];
        $tests['Add labels'] = [
            'issues',
            'issues.labeled.bug.json',
            [
                [
                    'event' => 'issue_event_labeled',
                    'action' => 'added required labels',
                ],
            ],
        ];
        $tests['Ignore labels'] = [
            'issues',
            'issues.labeled.feature.json',
            [
                [
                    'event' => 'issue_event_labeled',
                    'action' => 'ignored',
                ],
            ],
        ];
        $tests['Pull request on wrong repository'] = [
            'pull_request',
            'wrong_repository.pull_request.json',
            [],
        ];
        $tests['Pull request synchronize'] = [
            'pull_request',
            'pull_request.synchronize.json',
            [
                [
                    'event' => 'pr_edited',
                    'action' => 'preston validation commit comment removed',
                ],
            ],
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
