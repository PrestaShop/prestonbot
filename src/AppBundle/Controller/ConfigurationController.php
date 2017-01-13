<?php

namespace AppBundle\Controller;

use AppBundle\BotAction\BotAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends Controller
{
    /**
     * @Route("/configure", name="configuration_page")
     */
    public function configureAction()
    {
        return $this->render('default/configuration.html.twig', []);
    }

    /**
     * @Route("/updateSettings", name="update_settings")
     */
    public function updateSettings(Request $request)
    {
        if ($request->isMethod('POST')) {
            $botActions = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            // @todo: to be extracted
            foreach ($botActions as $botId => $botEnabled) {
                $botAction = $this->get('app.bot_action.repository')->find($botId);

                if ($botAction instanceof BotAction) {
                    if ($botEnabled) {
                        $botAction->enable();
                    }else {
                        $botAction->disable();
                    }

                    $em->flush($botAction);
                }
            }

            return new Response();
        }
    }
}
