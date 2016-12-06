<?php

namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\SecureApplicationPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SecureApplicationPass(), PassConfig::TYPE_BEFORE_REMOVING, 10);
    }
}
