<?php

namespace Tests\Feature\Controller\Action;

use Laminas\Diactoros\ServerRequestFactory;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    public function test()
    {
        $httpKernel = $this->getHttpKernel();
        $request = ServerRequestFactory::fromGlobals(['REQUEST_METHOD' => 'GET']);
        $response = $httpKernel->handle($request);

        $this->assertJson($response->getBody());
        $this->assertEquals("Hello world!", $response->getPayload());
    }
}