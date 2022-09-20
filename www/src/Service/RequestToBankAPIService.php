<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RequestToBankAPIService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchTransferMoneyToBank(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );

        $statusCode = $response->getStatusCode();
        $content = $response->toArray();

        return [$statusCode, $content];
    }
}