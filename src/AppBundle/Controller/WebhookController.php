<?php

namespace AppBundle\Controller;

use AppBundle\Event\GitHubEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebhookController extends Controller
{
    /**
     * @Route("/webhooks/github", name="webhooks_github")
     * @Method("POST")
     */
    public function githubAction(GithubEvent $event = null)
    {
        if (null === $event) {
            return new JsonResponse('[err] event not found.');
        }
        $eventName = strtolower($event->getName()).'_'.$event->getEvent()->getAction();

        $this->get('logger')->info(sprintf('[Event] %s (%s) received',
            $event->getName(),
            $event->getEvent()->getAction()
        ));
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return new JsonResponse($event->getStatuses());
    }
}
