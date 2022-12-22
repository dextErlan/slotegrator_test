<?php

namespace Tests\Feature\Controller\Action;

use Laminas\Diactoros\ServerRequestFactory;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    public function test(): void
    {
        $httpKernel = $this->getHttpKernel();
        $request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET']);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        if (method_exists($response, 'getPayload')) {
            $this->assertEquals("Hello world!", $response->getPayload());
        }
    }
}