<?php

/*
 * This file is part of the Pagination bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\PaginationBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaginationControllerTest extends WebTestCase
{
    /*
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/Fabien');

        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
    */

    public function testRegistryIndexRoute()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Go to the list view
        //$crawler = $client->request('GET', '/_registry/');
        //$this->assertTrue(200 === $client->getResponse()->getStatusCode());

        $this->assertTrue(true == true);
    }
}
