<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class WebhookController extends Controller
{
    /**
     * @Route("/webhooks/github", name="webhooks_github")
     * @Method("POST")
     */
    public function githubAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new \Exception('Invalid JSON body!');
        }
        
        $event = $this->get('app.webhook_resolver')->resolve($data);
        $listener = $this->get('app.issue_listener');
        $responseData = [];
        // found a smarter way to do this ...
        $enableLabels = $this->getParameter('enable_labels');
        
        
        // todo dispatch a GithubEvent and remove logic from controller
        switch ($event::name()) {
            case 'IssueCommentEvent':
                if($enableLabels) {
                    $responseData = [
                        'issue' => $event->issue->getNumber(),
                        'status_change' => $listener->handleCommentAddedEvent(
                            $event->issue->getNumber(),
                            $event->comment->getBody()
                        ),
                    ];
                }
                
                break;
            case 'PullRequestEvent':
                switch ($event->action) {
                    case 'opened':
                        if ($enableLabels) {
                            $responseData = [
                                'pull_request' => $event->pullRequest->getNumber(),
                                'status_change' => $listener->handlePullRequestCreatedEvent(
                                    $event->pullRequest->getNumber()
                                ),
                            ];
                        }
                        
                        
                        $this->get('app.pullrequest_listener')->checkForDescription($event->pullRequest, $event->pullRequest->getCommitSha());
                        $responseData = [
                            'issue' => $event->issue->getNumber(),
                            'status_change' => 'PR checked',
                        ];
                        break;
                        
                    default:
                        $responseData = [
                            'unsupported_action' => $event->action,
                        ];
                }
                break;
            case 'IssuesEvent':
            case 'IssueEvent':
                switch ($event->action) {
                    case 'labeled':
                        $responseData = [
                            'issue' => $event->issue->getNumber(),
                            'status_change' => $listener->handleLabelAddedEvent(
                                $event->issue->getNumber(),
                                $event->label->getName()
                            ),
                        ];
                        break;
                    default:
                        $responseData = [
                            'unsupported_action' => $event->action,
                        ];
                }
                break;
            default:
                $responseData = [
                    'unsupported_event' => $event::name(),
                ];
        }

        return new JsonResponse($responseData);
        // log something to the database?
    }
}
