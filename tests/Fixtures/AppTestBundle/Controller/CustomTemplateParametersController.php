<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Tests\Fixtures\AppTestBundle\Controller;

use FanOfSymfony\Bundle\EasyAdminBundle\Controller\AdminController as EasyAdminController;

class CustomTemplateParametersController extends EasyAdminController
{
    protected function renderTemplate($actionName, $templatePath, array $parameters = [])
    {
        $parameters['custom_parameter'] = $actionName;

        return parent::renderTemplate($actionName, $templatePath, $parameters);
    }
}
