<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class InjectUserCheckerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $firewallName = $container->getParameter('easyadmin.firewall_name');
        $loginManager = $container->findDefinition('easyadmin.login_manager');
        if ($container->has('security.user_checker.'.$firewallName)) {
            $loginManager->replaceArgument(1, new Reference('security.user_checker.'.$firewallName));
        }
    }
}