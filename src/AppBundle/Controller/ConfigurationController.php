<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Bot\BotAction;
use AppBundle\Bot\BotActionType;

class ConfigurationController extends Controller
{
    /**
     * @Route("/configure", name="configuration_page")
     */
    public function configureAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle\Bot\BotAction');
        $actionsBot = $repository->findAll();
        $actionsForms = [];

        foreach ($actionsBot as $actionBot) {
            $actionsForms[] = $this->createForm(BotActionType::class, $actionBot)->createView();
        }

        return $this->render('default/configuration.html.twig', [
            'actionsBot' => $actionsBot,
            'forms' => $actionsForms
        ]);
    }
}
