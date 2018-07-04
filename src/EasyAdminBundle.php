<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler\EasyAdminConfigPass;
use FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler\EasyAdminFormTypePass;
use FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler\TwigPathPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class EasyAdminBundle extends Bundle
{
    const VERSION = '1.17.14-DEV';

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwigPathPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new EasyAdminFormTypePass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new EasyAdminConfigPass());

        $this->addRegisterMappingsPass($container);
    }

    /**
     * Register storage mapping for model-based persisted objects from EasyAdminExtension.
     * Much inspired from FOSUserBundle implementation.
     *
     * @see  https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/FOSUserBundle.php
     *
     * @param ContainerBuilder $container
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $easyAdminExtensionBundlePath = dirname((new \ReflectionClass($this))->getFileName());
        $easyAdminExtensionDoctrineMapping = $easyAdminExtensionBundlePath.'/Resources/config/doctrine-mapping';

        $mappings = array(
            realpath($easyAdminExtensionDoctrineMapping) => 'FanOfSymfony\Bundle\EasyAdminBundle\Model',
        );

        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings));
        }
    }
}
