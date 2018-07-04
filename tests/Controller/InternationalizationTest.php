<?php

namespace FanOfSymfony\Bundle\EasyAdminBundle\Tests\Controller;

use FanOfSymfony\Bundle\EasyAdminBundle\Tests\Fixtures\AbstractTestCase;

class InternationalizationTest extends AbstractTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient(['environment' => 'internationalization']);
    }

    public function testLanguageDefinedByLayout()
    {
        $crawler = $this->getBackendHomepage();

        $this->assertSame('fr', trim($crawler->filter('html')->attr('lang')));
    }
}
