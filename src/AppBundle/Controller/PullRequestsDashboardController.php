<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Github\Exception\RuntimeException;

class PullRequestsDashboardController extends Controller
{
    /**
     * @Route("/dashboard/pull_requests", name="dashboard_pull_requests")
     * @Cache(expires="tomorrow", public=true)
     */
    public function indexAction()
    {
        try {
            $reporter = $this->get('app.pull_requests.reporter');
            $reports = [
                'develop' => $reporter->reportActivity('develop'),
                'legacy (1.6.1.x)' => $reporter->reportActivity('1.6.1.x'),
            ];
        }catch(RunTimeException $exception) {
            $this->addFlash(
                'danger',
                'Quota API GitHub dépassé, revenez plus tard...'
            );
            
            return $this->redirectToRoute('home_page');
        }

        return $this->render('default/pull_requests.html.twig', [
            'reports' => $reports,
            ]
        );
    }
}
