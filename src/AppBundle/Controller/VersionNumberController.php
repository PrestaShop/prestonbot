<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
