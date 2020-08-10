<?php

namespace AppBundle\Twig\Extension;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(Environment $environment)
    {
        $environment->registerUndefinedFunctionCallback(function($name) {
            return new TwigFunction($name, [$this, 'doNothing']);
        });
        $environment->registerUndefinedFilterCallback(function($name) {
            return new TwigFilter($name, [$this, 'doNothing']);
        });
    }

    public function doNothing()
    {
    }
}