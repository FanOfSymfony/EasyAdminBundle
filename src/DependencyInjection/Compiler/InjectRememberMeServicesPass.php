<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InjectRememberMeServicesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $firewallName = $container->getParameter('easyadmin.firewall_name');
        $loginManager = $container->getDefinition('easyadmin.login_manager');

        if ($container->hasDefinition('security.authentication.rememberme.services.persistent.'.$firewallName)) {
            $loginManager->replaceArgument(4, new Reference('security.authentication.rememberme.services.persistent.'.$firewallName));
        } elseif ($container->hasDefinition('security.authentication.rememberme.services.simplehash.'.$firewallName)) {
            $loginManager->replaceArgument(4, new Reference('security.authentication.rememberme.services.simplehash.'.$firewallName));
        }
    }
}