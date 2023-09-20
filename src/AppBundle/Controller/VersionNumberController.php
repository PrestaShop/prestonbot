<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VersionNumberController
{
    /**
     * @Route("/version", name="version_number")
     */
    public function getVersionNumberAction()
    {
        return new JsonResponse(\AppKernel::PRESTON_BOT_VERSION);
    }
}
