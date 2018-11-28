<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testGeneratetoken()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/generateToken');
    }

}
