<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends AbstractAdminController
{
    /**
     * {@inheritdoc}
     *
     * @throws AccessDeniedException
     */
    protected function isActionAllowed($actionName)
    {
        // autocomplete and embeddedList action are mapped to list action for access permissions
        if (in_array($actionName, ['autocomplete', 'embeddedList'])) {
            $actionName = 'list';
        }

        $this->get('easyadmin.admin_authorization_checker')->checksUserAccess(
            $this->entity,
            $actionName
        );

        return parent::isActionAllowed($actionName);
    }
}
