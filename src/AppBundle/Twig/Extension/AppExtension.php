<?php

namespace AppBundle\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    public function __construct(Environment $environment)
    {
        $environment->registerUndefinedFunctionCallback(function () {});
        $environment->registerUndefinedFilterCallback(function () {});
    }

    public function doNothing()
    {
    }
}
