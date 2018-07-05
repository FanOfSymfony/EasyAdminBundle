<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle;

use Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler\CheckForSessionPass;
use FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler\InjectRememberMeServicesPass;
use FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection\Compiler\InjectUserCheckerPass;
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
        parent::build($container);

        $container->addCompilerPass(new TwigPathPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        $container->addCompilerPass(new EasyAdminFormTypePass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new EasyAdminConfigPass());


        $container->addCompilerPass(new InjectUserCheckerPass());
        $container->addCompilerPass(new InjectRememberMeServicesPass());
        $container->addCompilerPass(new CheckForSessionPass());
//        $container->addCompilerPass(new CheckForMailerPass());
        $this->addRegisterMappingsPass($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function addRegisterMappingsPass(ContainerBuilder $container)
    {
        $mappings = array(
            realpath(__DIR__.'/Resources/config/doctrine-mapping') => 'FanOfSymfony\Bundle\EasyAdminBundle\Model',
        );
        if (class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array('easyadmin.model_manager_name'), 'easyadmin.backend_type_orm'));
        }
        if (class_exists('Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass')) {
            $container->addCompilerPass(DoctrineMongoDBMappingsPass::createXmlMappingDriver($mappings, array('easyadmin.model_manager_name'), 'easyadmin.backend_type_mongodb'));
        }
        if (class_exists('Doctrine\Bundle\CouchDBBundle\DependencyInjection\Compiler\DoctrineCouchDBMappingsPass')) {
            $container->addCompilerPass(DoctrineCouchDBMappingsPass::createXmlMappingDriver($mappings, array('easyadmin.model_manager_name'), 'easyadmin.backend_type_couchdb'));
        }
    }
}
