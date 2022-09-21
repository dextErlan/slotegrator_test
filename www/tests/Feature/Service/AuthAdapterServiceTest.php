<?php

namespace Tests\Feature\Service;

use App\Entity\User;
use App\Service\AuthAdapterService;
use Laminas\Authentication\Result;
use Tests\TestCase;

class AuthAdapterServiceTest extends TestCase
{
    public function testIdentityNotFound()
    {
        $em = $this->getEntityManager();

        $authAdapterService = new AuthAdapterService($em->getRepository(User::class), 'second@user.test', 'password2');
        $result = $authAdapterService->authenticate();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEmpty($result->getIdentity());
        $this->assertEquals(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

    public function testAuthenticateSuccess()
    {
        $em = $this->getEntityManager();

        $authAdapterService = new AuthAdapterService($em->getRepository(User::class), 'first@user.test', 'password');
        $result = $authAdapterService->authenticate();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotEmpty($result->getIdentity());
    }

    public function testPasswordNotValid()
    {
        $em = $this->getEntityManager();

        $authAdapterService = new AuthAdapterService($em->getRepository(User::class), 'first@user.test', 'password2');
        $result = $authAdapterService->authenticate();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEmpty($result->getIdentity());
        $this->assertEquals(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
    }
}