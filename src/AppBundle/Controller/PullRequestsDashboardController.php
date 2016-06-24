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
        $reporter = $this->get('app.pull_requests.reporter');
        $reports = [
            'develop' => $reporter->reportActivity('develop'),
            'legacy (1.6.1.x)' => $reporter->reportActivity('1.6.1.x'),
        ];

        return $this->render('default/pull_requests.html.twig', [
            'reports' => $reports,
            ]
        );
    }
}
