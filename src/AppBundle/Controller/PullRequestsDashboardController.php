<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use AppBundle\PullRequests\Labels;

class PullRequestsDashboardController extends Controller
{
    /**
     * @Route("/dashboard/pull_requests")
     * @Cache(expires="tomorrow", public=true)
     */
    public function indexAction()
    {
        $toBeCodeReviewed = $this->findAll(Labels::WAITING_FOR_CODE_REVIEW);
        $toBeQAFeedback = $this->findAll(Labels::WAITING_FOR_QA_FEEDBACK);
        $toBePMFeedback = $this->findAll(Labels::WAITING_FOR_PM_FEEDBACK);
        $silentContribs = $this->findAll('');
        
        return $this->render('default/pull_requests.html.twig', [
            'waitingForCodeReviewsContribs' => $toBeCodeReviewed,
            'waitingForQAContribs' => $toBeQAFeedback,
            'waitingForPMContribs' => $toBePMFeedback,
            'silentContribs' => $silentContribs,
        ]);
    }
    
    private function findAll($tagName)
    {
        $repository = $this->get('app.pull_request.repository');
        
        return $repository->findAllWithTag($tagName);
    }
}
