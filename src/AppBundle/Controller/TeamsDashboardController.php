<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class TeamsDashboardController extends Controller
{
    /**
     * @Route("/dashboard/teams2")
     * @Cache(expires="tomorrow", public=true)
     */
    public function indexAction()
    {
        $report = $this->get('app.teams.reporter')->reportTeamsAndMembers();

        return $this->render('default/teams.html.twig', ['teams' => $report]);
    }
}
