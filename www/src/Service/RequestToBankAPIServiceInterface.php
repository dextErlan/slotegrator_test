<?php

namespace App\Service;

use App\Exception\BankAPIException;

interface RequestToBankAPIServiceInterface
{
    /**
     * Перевод денег на счет в банке.
     *
     * @param int $sum
     * @param string $accountNumber
     * @throws BankAPIException
     *
     * @return array{int, array<mixed>}
     */
    public function fetchTransferMoneyToBank(int $sum, string $accountNumber): array;
}