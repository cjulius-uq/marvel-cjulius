<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CharacterControllerTest extends WebTestCase
{
    public function testGet()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/character/get');
    }

    public function testList()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/character/list');
    }

}
