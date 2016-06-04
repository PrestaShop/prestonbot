<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lpdigital\Github\EventType\ActionableEventInterface;
use AppBundle\Event\GitHubEvent;


class WebhookController extends Controller
{
    /**
     * @Route("/webhooks/github", name="webhooks_github")
     * @Method("POST")
     */
    public function githubAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $responseData = [];
        
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid JSON body'], 500);
        }

        $event = $this->get('app.webhook_resolver')->resolve($data);
        $githubEvent = new GitHubEvent($event::name(), $event);
        
        if ($event instanceof ActionableEventInterface) {
            $eventName = strtolower($event::name()).'_'.$event->getAction();

            $this->get('event_dispatcher')->dispatch($eventName, $githubEvent);
            $responseData = $githubEvent->getStatuses();
        }

        return new JsonResponse($responseData);
    }
}
