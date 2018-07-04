<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Form\Transformer;

use FanOfSymfony\Bundle\EasyAdminBundle\Helper\EditableRolesHelper;
use Symfony\Component\Form\DataTransformerInterface;

class RestoreRolesTransformer implements DataTransformerInterface
{
    /**
     * @var array|null
     */
    protected $originalRoles = null;

    /**
     * @var EditableRolesHelper|null
     */
    protected $rolesBuilder = null;

    /**
     * @param EditableRolesHelper $rolesBuilder
     */
    public function __construct(EditableRolesHelper $rolesBuilder)
    {
        $this->rolesBuilder = $rolesBuilder;
    }

    /**
     * @param array|null $originalRoles
     */
    public function setOriginalRoles(array $originalRoles = null)
    {
        $this->originalRoles = $originalRoles ?: array();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return $value;
        }
        if (null === $this->originalRoles) {
            throw new \RuntimeException('Invalid state, originalRoles array is not set');
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($selectedRoles)
    {
        if (null === $this->originalRoles) {
            throw new \RuntimeException('Invalid state, originalRoles array is not set');
        }
        $availableRoles = $this->rolesBuilder->getRoles();
        $hiddenRoles = array_diff($this->originalRoles, array_keys($availableRoles));

        return array_merge($selectedRoles, $hiddenRoles);
    }
}
