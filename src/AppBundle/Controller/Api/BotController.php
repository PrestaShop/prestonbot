<?php

namespace AppBundle\Controller\Api;

use AppBundle\Bot\BotAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BotController extends Controller
{
    /**
     * @Route("/bot", name="api_actions_all")
     */
    public function getAllAction()
    {
        $actionsBot = $this->get('app.bot.repository')->findAll();

        return $this->json($actionsBot);
    }

    /**
     * @Route("/bot/{id}", name="api_actions_get")
     *
     * @param mixed $id
     */
    public function getAction($id)
    {
        $actionBot = $this->getDoctrine($id)
            ->getRepository('AppBundle\Bot\BotAction')
            ->find($id)
        ;

        if ($actionBot instanceof BotAction) {
            return $this->json($actionBot);
        }

        throw $this->createNotFoundException(sprintf('The botAction with id %s does not exist', $id));
    }
}
