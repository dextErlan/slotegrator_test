<?php

use App\HttpKernel;
use Laminas\Diactoros\ServerRequestFactory;

require __DIR__ . '/../vendor/autoload.php';

$container = require 'config/container.php';

$httpKernel = $container->get(HttpKernel::class);

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$response = $httpKernel->handle($request);

// emit the response
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
http_response_code($response->getStatusCode());
echo $response->getBody();