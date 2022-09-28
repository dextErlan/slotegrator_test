<?php

namespace App\Service\Money;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionConvert;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Exception\BankAccountNotForUser;
use App\Exception\ChangeTransactionStatusException;

class MoneyTransactionService
{
    private array $statusSequenceForMoneyTransactionConvert = [
        MoneyTransactionConvert::STATUS_OPEN => [
            MoneyTransactionConvert::STATUS_SUCCESS,
            MoneyTransactionConvert::STATUS_REFUND,
        ],
    ];
    private array $statusSequenceForMoneyTransactionToBank = [
        MoneyTransactionToBank::STATUS_OPEN => [
            MoneyTransactionToBank::STATUS_SUCCESS,
            MoneyTransactionToBank::STATUS_REFUND,
        ],
    ];

    /**
     * Запись таска на конвертацию.
     *
     * @param User $user
     * @param int $sum
     *
     * @return MoneyTransactionConvert
     */
    public function openConvertTransaction(User $user, int $sum): MoneyTransactionConvert
    {
        $exchangeRate = $this->getExchangeRate();

        $moneyTransactionConvert = new MoneyTransactionConvert();
        $moneyTransactionConvert->setUser($user);
        $moneyTransactionConvert->setExchangeRate($exchangeRate);
        $moneyTransactionConvert->setSum($sum);
        $moneyTransactionConvert->setStatus(MoneyTransactionConvert::STATUS_OPEN);

        return $moneyTransactionConvert;
    }

    public function getExchangeRate()
    {
        $config = require __DIR__ . '/../../../config/exchangeRate.php';

        return $config['exchangeRate'];
    }

    /**
     * Запись таска на перевод денег в банк.
     *
     * @param User $user
     * @param int $sum
     * @param BankAccount $bankAccount
     *
     * @return MoneyTransactionToBank
     * @throws BankAccountNotForUser
     */
    public function openTransferTransaction(User $user, int $sum, BankAccount $bankAccount): MoneyTransactionToBank
    {
        if (! $bankAccount->isBelongsToUser($user)) {
            throw new BankAccountNotForUser(
                sprintf(
                    "Банковский счет %s  не найден у пользователя с email = %s",
                    $bankAccount->getAccountNumber(),
                    $user->getEmail()
                )
            );
        }

        $moneyTransactionToBank = new MoneyTransactionToBank();
        $moneyTransactionToBank->setUser($user);
        $moneyTransactionToBank->setBankAccount($bankAccount);
        $moneyTransactionToBank->setSum($sum);
        $moneyTransactionToBank->setStatus(MoneyTransactionToBank::STATUS_OPEN);

        return $moneyTransactionToBank;
    }

    /**
     * @param MoneyTransactionToBank $transactionToBank
     * @param string $newStatus
     * @throws ChangeTransactionStatusException
     */
    public function changeTransferTransactionStatus(MoneyTransactionToBank $transactionToBank, string $newStatus): void
    {
        $currentStatus = $transactionToBank->getStatus();

        if (
            ! isset($this->statusSequenceForMoneyTransactionToBank[$currentStatus])
            || ! in_array($newStatus, $this->statusSequenceForMoneyTransactionToBank[$currentStatus])
        ) {
            throw new ChangeTransactionStatusException(
                sprintf(
                    'Ошибка смены статуса с %s на %s в MoneyTransactionToBank id=%d',
                    $currentStatus,
                    $newStatus,
                    $transactionToBank->getId()
                )
            );
        }

        $transactionToBank->setStatus($newStatus);
    }

    /**
     * @param MoneyTransactionConvert $transactionConvert
     * @param $newStatus
     * @throws ChangeTransactionStatusException
     */
    public function changeConvertTransactionStatus(MoneyTransactionConvert $transactionConvert,  $newStatus): void
    {
        $currentStatus = $transactionConvert->getStatus();

        if (
            ! isset($this->statusSequenceForMoneyTransactionConvert[$currentStatus])
            || ! in_array($newStatus, $this->statusSequenceForMoneyTransactionConvert[$currentStatus])
        ) {
            throw new ChangeTransactionStatusException("Ошибка смены статуса с $currentStatus на $newStatus в MoneyTransactionConvert id={$transactionConvert->getId()}");
        }

        $transactionConvert->setStatus($newStatus);
    }
}