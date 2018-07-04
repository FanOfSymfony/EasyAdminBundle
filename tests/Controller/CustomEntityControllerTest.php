<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Tests\Controller;

use FanOfSymfony\Bundle\EasyAdminBundle\Tests\Fixtures\AbstractTestCase;

class CustomEntityControllerTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(['environment' => 'custom_entity_controller']);
    }

    public function testListAction()
    {
        $this->requestListView();
        $this->assertContains('Overridden list action.', $this->client->getResponse()->getContent());
    }

    public function testShowAction()
    {
        $this->requestShowView();
        $this->assertContains('Overridden show action.', $this->client->getResponse()->getContent());
    }
}
