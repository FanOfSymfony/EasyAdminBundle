<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Configuration;

class ShortFormTypeConfigPass implements ConfigPassInterface
{
    private $customFormTypes = array();

    private static $configWithFormKeys = array('form', 'edit', 'new');
    private static $nativeShortFormTypes = array(
        'embedded_list' => 'FanOfSymfony\Bundle\EasyAdminBundle\Form\Type\EasyAdminEmbeddedListType',
        'admin_roles' => 'FanOfSymfony\Bundle\EasyAdminBundle\Form\Type\Security\AdminRolesType',
    );

    public function __construct(array $customFormTypes = array())
    {
        $this->customFormTypes = $customFormTypes;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->replaceShortNameTypes($backendConfig);

        return $backendConfig;
    }

    protected function replaceShortNameTypes(array $backendConfig)
    {
        if (
            !isset($backendConfig['entities'])
            || !is_array($backendConfig['entities'])
        ) {
            return $backendConfig;
        }

        foreach ($backendConfig['entities'] as &$entity) {
            $entity = $this->replaceShortFormTypesInEntityConfig($entity);
        }

        return $backendConfig;
    }

    protected function replaceShortFormTypesInEntityConfig(array $entity)
    {
        $shortFormTypes = $this->getShortFormTypes();

        foreach (static::$configWithFormKeys as $configWithFormKey) {
            if (
                isset($entity[$configWithFormKey])
                && isset($entity[$configWithFormKey]['fields'])
                && is_array($entity[$configWithFormKey]['fields'])
            ) {
                foreach ($entity[$configWithFormKey]['fields'] as $name => $field) {
                    if (!isset($field['type'])) {
                        continue;
                    }

                    if (in_array($field['type'], array_keys($shortFormTypes))) {
                        $entity[$configWithFormKey]['fields'][$name]['type'] = $shortFormTypes[$field['type']];
                    }
                }
            }
        }

        return $entity;
    }

    private function getShortFormTypes()
    {
        return array_merge(static::$nativeShortFormTypes, $this->customFormTypes);
    }
}
