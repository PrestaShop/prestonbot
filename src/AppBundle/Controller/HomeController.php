<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home_page")
     */
    public function homepageAction()
    {
        $statusApi = $this->get('app.status_api');

        return $this->render('default/homepage.html.twig', [
            'needsReviewUrl' => $statusApi->getNeedsReviewUrl(),
            'waitingForQAUrl' => $statusApi->getWaitingForQAUrl(),
            'waitingForPMUrl' => $statusApi->getWaitingForPMUrl(),
        ]);
    }
}
