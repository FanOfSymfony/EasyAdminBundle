<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Resolves all the backend configuration values and most of the entities
 * configuration. The information that must resolved during runtime is handled
 * by the Configurator class.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class EasyAdminExtension extends Extension
{
    /**
     * @var array
     */
    private static $doctrineDrivers = array(
        'orm' => array(
            'registry' => 'doctrine',
            'tag' => 'doctrine.event_subscriber',
        ),
        'mongodb' => array(
            'registry' => 'doctrine_mongodb',
            'tag' => 'doctrine_mongodb.odm.event_subscriber',
        ),
        'couchdb' => array(
            'registry' => 'doctrine_couchdb',
            'tag' => 'doctrine_couchdb.event_subscriber',
            'listener_class' => 'FOS\UserBundle\Doctrine\CouchDB\UserListener',
        ),
    );

    private $mailerNeeded = false;
    private $sessionNeeded = false;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $this->processConfigFiles($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);


        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $container->setParameter('easyadmin.config', $config);
        $container->setParameter('easyadmin.cache.dir', $container->getParameter('kernel.cache_dir').'/easy_admin');
        $container->setParameter('easyadmin.custom_form_types', $config['custom_form_types']);
        $container->setParameter('easyadmin.minimum_role', $config['minimum_role']);

        $container->setParameter(
            'easyadmin.embedded_list.open_new_tab',
            $config['embedded_list']['open_new_tab']
        );

        /**
         * Security
         */

        if ('custom' !== $config['db_driver']) {
            if (isset(self::$doctrineDrivers[$config['db_driver']])) {
                $loader->load('doctrine.xml');
                $container->setAlias('easyadmin.doctrine_registry', new Alias(self::$doctrineDrivers[$config['db_driver']]['registry'], false));
            } else {
                $loader->load(sprintf('%s.xml', $config['db_driver']));
            }

            $container->setParameter('easyadmin'.'.backend_type_'.$config['db_driver'], true);
        }

        if (isset(self::$doctrineDrivers[$config['db_driver']])) {
            $definition = $container->getDefinition('easyadmin.object_manager');

            $definition->setFactory(array(new Reference('easyadmin.doctrine_registry'), 'getManager'));
        }

        foreach (array( 'services', 'form', 'security', 'util', 'listeners') as $basename) {
            $loader->load(sprintf('%s.xml', $basename));
        }

        if (!$config['use_authentication_listener']) {
            $container->removeDefinition('easyadmin.listener.authentication');
        }

//        if ($config['use_flash_notifications']) {
//            $this->sessionNeeded = true;
//            $loader->load('flash_notifications.xml');
//        }




        $container->setAlias('easyadmin.util.email_canonicalizer', $config['service']['email_canonicalizer']);
        $container->setAlias('easyadmin.util.username_canonicalizer', $config['service']['username_canonicalizer']);
        $container->setAlias('easyadmin.util.token_generator', $config['service']['token_generator']);
        $container->setAlias('easyadmin.user_manager', new Alias($config['service']['user_manager'], true));


        if ($config['use_listener'] && isset(self::$doctrineDrivers[$config['db_driver']])) {
            $listenerDefinition = $container->getDefinition('easyadmin.user_listener');
            $listenerDefinition->addTag(self::$doctrineDrivers[$config['db_driver']]['tag']);
            if (isset(self::$doctrineDrivers[$config['db_driver']]['listener_class'])) {
                $listenerDefinition->setClass(self::$doctrineDrivers[$config['db_driver']]['listener_class']);
            }
        }

        $this->remapParametersNamespaces($config, $container, array(
            '' => array(
                'db_driver' => 'easy_admin.storage',
                'firewall_name' => 'easyadmin.firewall_name',
                'model_manager_name' => 'easyadmin.model_manager_name',
                'user_class' => 'easyadmin.user_class',
            ),
        ));

        if (!empty($config['registration'])) {
            $this->loadRegistration($config['registration'], $container, $loader, $config['from_email']);
        }
        
        // load bundle's services

        if ($this->sessionNeeded) {
            // Use a private alias rather than a parameter, to avoid leaking it at runtime (the private alias will be removed)
            $container->setAlias('easyadmin.session', new Alias('session', false));
        }

        if ($container->getParameter('kernel.debug')) {
            // in 'dev', use the built-in Symfony exception listener
            $container->removeDefinition('easyadmin.listener.exception');
            // avoid parsing the entire config in 'dev' (even for requests unrelated to the backend)
            $container->removeDefinition('easyadmin.cache.config_warmer');
        }

        if ($container->hasParameter('locale')) {
            $container->getDefinition('easyadmin.configuration.design_config_pass')
                ->replaceArgument(2, $container->getParameter('locale'));
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param XmlFileLoader    $loader
     * @param array            $fromEmail
     */
    private function loadRegistration(array $config, ContainerBuilder $container, XmlFileLoader $loader, array $fromEmail)
    {
        $loader->load('registration.xml');
        $this->sessionNeeded = true;
        if ($config['confirmation']['enabled']) {
            $this->mailerNeeded = true;
            $loader->load('email_confirmation.xml');
        }
        if (isset($config['confirmation']['from_email'])) {
            // overwrite the global one
            $fromEmail = $config['confirmation']['from_email'];
            unset($config['confirmation']['from_email']);
        }
        $container->setParameter('easyadmin.registration.confirmation.from_email', array($fromEmail['address'] => $fromEmail['sender_name']));
        $this->remapParametersNamespaces($config, $container, array(
            'confirmation' => 'easyadmin.registration.confirmation.%s',
            'form' => 'easyadmin.registration.form.%s',
        ));
    }

    /**
     * This method allows to define the entity configuration is several files.
     * Without this, Symfony doesn't merge correctly the 'entities' config key
     * defined in different files.
     *
     * @param array $configs
     *
     * @return array
     */
    private function processConfigFiles(array $configs)
    {
        $existingEntityNames = [];

        foreach ($configs as $i => $config) {
            if (array_key_exists('entities', $config)) {
                $processedConfig = [];

                foreach ($config['entities'] as $key => $value) {
                    $entityConfig = $this->normalizeEntityConfig($key, $value);
                    $entityName = $this->getUniqueEntityName($key, $entityConfig, $existingEntityNames);
                    $entityConfig['name'] = $entityName;

                    $processedConfig[$entityName] = $entityConfig;

                    $existingEntityNames[] = $entityName;
                }

                $config['entities'] = $processedConfig;
            }

            $configs[$i] = $config;
        }

        return $configs;
    }

    /**
     * Transforms the two simple configuration formats into the full expanded
     * configuration. This allows to reuse the same method to process any of the
     * different configuration formats.
     *
     * These are the two simple formats allowed:
     *
     * # Config format #1: no custom entity name
     * easy_admin:
     *     entities:
     *         - AppBundle\Entity\User
     *
     * # Config format #2: simple config with custom entity name
     * easy_admin:
     *     entities:
     *         User: AppBundle\Entity\User
     *
     * And this is the full expanded configuration syntax generated by this method:
     *
     * # Config format #3: expanded entity configuration with 'class' parameter
     * easy_admin:
     *     entities:
     *         User:
     *             class: AppBundle\Entity\User
     *
     * @param mixed $entityName
     * @param mixed $entityConfig
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    private function normalizeEntityConfig($entityName, $entityConfig)
    {
        // normalize config formats #1 and #2 to use the 'class' option as config format #3
        if (!\is_array($entityConfig)) {
            $entityConfig = ['class' => $entityConfig];
        }

        // if config format #3 is used, ensure that it defines the 'class' option
        if (!isset($entityConfig['class'])) {
            throw new \RuntimeException(sprintf('The "%s" entity must define its associated Doctrine entity class using the "class" option.', $entityName));
        }

        return $entityConfig;
    }

    /**
     * The name of the entity is included in the URLs of the backend to define
     * the entity used to perform the operations. Obviously, the entity name
     * must be unique to identify entities unequivocally.
     *
     * This method ensures that the given entity name is unique among all the
     * previously existing entities passed as the second argument. This is
     * achieved by iteratively appending a suffix until the entity name is
     * guaranteed to be unique.
     *
     * @param string $entityName
     * @param array  $entityConfig
     * @param array  $existingEntityNames
     *
     * @return string The entity name transformed to be unique
     */
    private function getUniqueEntityName($entityName, array $entityConfig, array $existingEntityNames)
    {
        // the shortcut config syntax doesn't require to give entities a name
        if (is_numeric($entityName)) {
            $entityClassParts = explode('\\', $entityConfig['class']);
            $entityName = end($entityClassParts);
        }

        $i = 2;
        $uniqueName = $entityName;
        while (\in_array($uniqueName, $existingEntityNames)) {
            $uniqueName = $entityName.($i++);
        }

        $entityName = $uniqueName;

        // make sure that the entity name is valid as a PHP method name
        // (this is required to allow extending the backend with a custom controller)
        if (!$this->isValidMethodName($entityName)) {
            throw new \InvalidArgumentException(sprintf('The name of the "%s" entity contains invalid characters (allowed: letters, numbers, underscores; the first character cannot be a number).', $entityName));
        }

        return $entityName;
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $map
     */
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName) {
            if (array_key_exists($name, $config)) {
                $container->setParameter($paramName, $config[$name]);
            }
        }
    }
    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @param array            $namespaces
     */
    protected function remapParametersNamespaces(array $config, ContainerBuilder $container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }
            if (is_array($map)) {
                $this->remapParameters($namespaceConfig, $container, $map);
            } else {
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }

    /**
     * Checks whether the given string is valid as a PHP method name.
     *
     * @param string $name
     *
     * @return bool
     */
    private function isValidMethodName($name)
    {
        return 0 !== preg_match('/^-?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name);
    }
}
