<?php

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    /**
     * @dataProvider getTests
     *
     * @param mixed $eventHeader
     * @param mixed $payloadFilename
     * @param mixed $expectedResponse
     * @param mixed $expectedHttpStatusCode
     */
    public function testActions($eventHeader, $payloadFilename, $expectedResponse, $expectedHttpStatusCode = 200)
    {
        $client = $this->createClient();
        $client->enableProfiler();
        $gihubToken = static::$kernel
            ->getContainer()
            ->getParameter('github_secured_token')
        ;

        $errorsMessage = null;

        $body = file_get_contents(__DIR__.'/../webhook_examples/'.$payloadFilename);

        $signature = $this->createSignature($body, $gihubToken);

        $client->request('POST', '/webhooks/github', [], [], [
            'HTTP_X-Github-Event' => $eventHeader,
            'HTTP_X-Hub-Signature' => $signature,
        ], $body);
        $response = $client->getResponse();

        $errorsMessage = 'OK';
        if ($profile = $client->getProfile()) {
            $token = $response->headers->get('X-Debug-Token');
            $errorsMessage = $this->handleExceptionFromCollector($profile, $token);
        }
        $responseData = json_decode($response->getContent(), true);
        $this->assertSame($expectedHttpStatusCode, $response->getStatusCode(), $errorsMessage);

        // a weak sanity check that we went down "the right path" in the controller
        $this->assertSame($expectedResponse, $responseData);
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
                [
                    'event' => 'pr_opened',
                    'action' => 'checked for changes on Classic Theme',
                    'status' => 'not_found',
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
                [
                    'event' => 'pr_opened',
                    'action' => 'checked for changes on Classic Theme',
                    'status' => 'not_found',
                ],
            ],
        ];
        $tests['Pull request creation with classic changes'] = [
            'pull_request',
            'pull_request_opened_classic.json',
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
                    'status' => 'valid',
                ],
                [
                    'event' => 'pr_opened',
                    'action' => 'checked for changes on Classic Theme',
                    'status' => 'found',
                ],
            ],
        ];
        $tests['Pull request creation for critical bug'] = [
            'issues',
            'issues.labeled.bug.json',
            [
                [
                    'event' => 'issue_event_labeled',
                    'action' => 'added required labels',
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
            null,
            404,
        ];
        $tests['Status'] = [
            'pull_request',
            'status.json',
            null,
            404,
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

    private function handleExceptionFromCollector($profile, $token)
    {
        $exception = $profile->getCollector('exception');
        $trace = current($exception->getTrace());

        return $exception->getMessage()
            .' in '.$trace['file']
            .'(line '.$trace['line'].')'
            .' (token: '.$token.')'
        ;
    }

    /**
     * @param string $algo
     * @param string $signedContent
     * @param string $secret
     *
     * @return string
     */
    private function createSignature($signedContent, $secret = self::SECRET, $algo = 'sha1')
    {
        return sprintf('%s=%s', $algo, hash_hmac($algo, $signedContent, $secret));
    }
}
