<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Lpdigital\Github\EventType\ActionableEventInterface;
use AppBundle\Event\GitHubEvent;
use Lpdigital\Github\Exception\EventNotFoundException;

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
        try {
            $event = $this->get('app.webhook_resolver')->resolve($data);
        } catch (EventNotFoundException $e) {
            return new JsonResponse('[err] event not found.');
        }

        if ($event instanceof ActionableEventInterface &&
            $this->isValid($event)
        ) {
            $githubEvent = new GitHubEvent($event::name(), $event);
            $eventName = strtolower($event::name()).'_'.$event->getAction();

            $this->get('logger')->info(sprintf('[Event] %s (%s) received',
                $event::name(),
                $event->getAction()
            ));
            $this->get('event_dispatcher')->dispatch($eventName, $githubEvent);
            $responseData = $githubEvent->getStatuses();
        }else {
            $this->get('logger')->error(
                sprintf(
                    '[Event] %s received from `%s` repository',
                    $event::name(),
                    $event->getRepository()->getFullName()
                )
            );
        }

        return new JsonResponse($responseData);
    }

    private function isValid(ActionableEventInterface $event)
    {
        $repository = $event->getRepository();
        list($repositoryUsername, $repositoryName) = explode('/', $repository->getFullName());

        return $repositoryUsername === $this->getParameter('repository_username') &&
            $repositoryName === $this->getParameter('repository_name')
        ;
    }
}
