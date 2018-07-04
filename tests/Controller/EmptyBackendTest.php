<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Tests\Controller;

use FanOfSymfony\Bundle\EasyAdminBundle\Tests\Fixtures\AbstractTestCase;

class EmptyBackendTest extends AbstractTestCase
{
    public function testNoEntityHasBeenConfigured()
    {
        $this->initClient(['environment' => 'empty_backend']);
        $this->client->request('GET', '/admin/');

        $this->assertSame(500, $this->client->getResponse()->getStatusCode());
        $this->assertContains('NoEntitiesConfiguredException', $this->client->getResponse()->getContent());
    }
}
