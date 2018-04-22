<?php

namespace AppBundle\Controller;

use AppBundle\Event\GitHubEvent;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebhookController extends AbstractController
{
    /**
     * @Route("/webhooks/github", name="webhooks_github")
     * @Method("POST")
     */
    public function githubAction(?GitHubEvent $event, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher): JsonResponse
    {
        if (null === $event) {
            return new JsonResponse('[err] event not found.');
        }

        $eventName = strtolower($event->getName()).'_'.$event->getEvent()->getAction();

        $logger->info(sprintf('[Event] %s (%s) received',
            $event->getName(),
            $event->getEvent()->getAction()
        ));

        $eventDispatcher->dispatch($eventName, $event);

        return new JsonResponse($event->getStatuses());
    }
}
