<?php

namespace AppBundle\Controller;

use AppBundle\Issues\StatusApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function homepageAction(StatusApi $statusApi)
    {
        return $this->render(
            'default/homepage.html.twig',
            [
                'needsReviewUrl' => $statusApi->getNeedsReviewUrl(),
                'waitingForQAUrl' => $statusApi->getWaitingForQAUrl(),
                'waitingForPMUrl' => $statusApi->getWaitingForPMUrl(),
            ]
        );
    }
}
