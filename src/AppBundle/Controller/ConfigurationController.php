<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Bot\BotAction;

class ConfigurationController extends Controller
{
    /**
     * @Route("/configure", name="configuration_page")
     */
    public function configureAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle\Bot\BotAction');

        return $this->render('default/configuration.html.twig', [
            'actions' => $repository->findAll()
        ]);
    }
}
