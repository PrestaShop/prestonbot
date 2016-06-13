<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class PullRequestsDashboardController extends Controller
{
    /**
     * @Route("/dashboard/pull_requests")
     * @Cache(expires="tomorrow", public=true)
     */
    public function indexAction()
    {
        $report = $this->get('app.pull_requests.reporter')->reportActivity();

        return $this->render('default/pull_requests.html.twig', $report);
    }
}
