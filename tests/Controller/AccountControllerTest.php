<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    /**
     * @group functionalTest
     */
    public function testRedirectWhenNotLogged()
    {
        $client = static::createClient();
        $client->request('GET', '/phpinfo');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
