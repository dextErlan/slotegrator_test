<?php

namespace App\Service;

use App\Exception\ClientErrorResponseException;
use App\Exception\ServerErrorResponseException;
use App\Exception\TransferMoneyToBankException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RequestToBankAPIService implements RequestToBankAPIServiceInterface
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Перевод денег на счет в банке.
     *
     * @param int $sum
     * @param string $accountNumber
     * @return array{int, array<mixed>}
     * @throws ClientErrorResponseException
     * @throws ServerErrorResponseException
     * @throws TransferMoneyToBankException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function fetchTransferMoneyToBank(int $sum, string $accountNumber): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.github.com/repos/symfony/symfony-docs'
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            throw new ServerErrorResponseException("Ошибка в запросе к Bank API statusCode = $statusCode");
        }

        if ($statusCode >= 400) {
            throw new ClientErrorResponseException("Ошибка в запросе к Bank API statusCode = $statusCode");
        }

        $content = $response->toArray();

        if (isset($content['error'])) {
            throw new TransferMoneyToBankException("Запрос к Bank API с ошибками: " . implode(", ", $content['error']));
        }

        return [$statusCode, $content];
    }
}