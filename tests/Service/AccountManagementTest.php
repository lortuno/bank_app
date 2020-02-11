<?php

namespace App\Tests\Service;

use App\Service\AccountManagement;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class AccountManagementTest extends TestCase
{
    /**
     * @throws Exception
     * @group unitTest
     */
    public function testInstance()
    {
        $request = $this->getMockedRequest();
        $em = $this->getMockedEntityManager();

        $accountManagement = new AccountManagement($request, $em);

        $this->assertInstanceOf(AccountManagement::class, $accountManagement, 'hola');
    }

    /**
     * @return Request
     */
    private function getMockedRequest(): Request
    {
       $request = $this->getMockBuilder(Request::class)
            ->getMock();


        $request->method('get')->willReturn([
            'account_number' => 'test1234',
            'email' => 'test_client@example.com',
        ]);

        return $request;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getMockedEntityManager(): EntityManagerInterface
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();

        return $em;
    }

    public function testRemoveUserAccessToAccount()
    {

    }

    public function testRemoveAccount()
    {

    }
}
