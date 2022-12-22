<?php

namespace Tests\Feature\Controller\Action;

use Laminas\Diactoros\ServerRequestFactory;
use Tests\TestCase;

class GiveawayActionTest extends TestCase
{
    public function testNotAuthenticated(): void
    {
        $httpKernel = $this->getHttpKernel();

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/giveaway'
            ], [], []);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        if (method_exists($response, 'getPayload')) {
            $this->assertEquals('Необходимо войти в систему!', $response->getPayload());
        }
    }

    public function testGiveaway(): void
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
        $httpKernel->handle($request);

        $request = ServerRequestFactory::fromGlobals(
            [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/giveaway'
            ], [], []);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        if (method_exists($response, 'getPayload')) {
            $this->assertStringContainsString('Поздравляем, Вы выиграли ', $response->getPayload());
        }
    }
}