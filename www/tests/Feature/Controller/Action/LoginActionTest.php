<?php

namespace Tests\Feature\Controller\Action;

use Laminas\Diactoros\ServerRequestFactory;
use Tests\TestCase;

class LoginActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getAuthService()->clearIdentity();
    }

    public function testNotGetRequest()
    {
        $httpKernel = $this->getHttpKernel();
        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/login'
            ], [], []);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        $this->assertEquals("Страница не найдена!", $response->getPayload());
    }

    public function testFailedLogin()
    {
        $httpKernel = $this->getHttpKernel();
        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/login'
            ],
            [],
            [
                'email' => 'first@user.test',
                'password' => 'password2'
            ]);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        $this->assertEquals(['error' => ['Логин и пароль не совпадают!']], $response->getPayload());
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testNotSendPassword()
    {
        $httpKernel = $this->getHttpKernel();
        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/login'
            ],
            [],
            [
                'email' => 'first@user.test',
            ]);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        $this->assertEquals(['error' => ['Не переданы имя пользователя и пароль!']], $response->getPayload());
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testLogin()
    {
        $httpKernel = $this->getHttpKernel();
        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/login'
            ],
            [],
            [
                'email' => 'first@user.test',
                'password' => 'password'
            ]);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        $this->assertEquals("Вы вошли в систему!", $response->getPayload());

        $response = $httpKernel->handle($request);
        $this->assertEquals("Вы уже вошли в систему!", $response->getPayload());
    }
}