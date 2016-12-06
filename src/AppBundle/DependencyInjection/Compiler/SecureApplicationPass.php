<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * If a token is setup in configuration, enable a Request subscriber to
 * secure the application against forbidden calls from network.
 *
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class SecureApplicationPass implements CompilerPassInterface
{
    const VALIDATOR_SUBSCRIBER_ID = 'app.github_token_validator_subscriber';
    const GITHUB_SECURED_ID = 'github_secured_token';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (null !== $container->getParameter(self::GITHUB_SECURED_ID)) {
            $definition = $container->getDefinition(self::VALIDATOR_SUBSCRIBER_ID);
            $definition->addTag('kernel.event_subscriber', ['priority' => 10]);
        } else {
            $container->removeDefinition(self::VALIDATOR_SUBSCRIBER_ID);
        }
    }
}
