<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
