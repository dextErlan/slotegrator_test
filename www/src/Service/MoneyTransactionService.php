<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionConvert;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Exception\ChangeTransactionStatusException;
use Doctrine\ORM\EntityManagerInterface;

class MoneyTransactionService
{
    const STATUS_OPEN = 'open';
    const STATUS_SUCCESS = 'success';
    const STATUS_REFUND = 'refund';

    private array $statusSequence = [
        self::STATUS_OPEN => [self::STATUS_SUCCESS, self::STATUS_REFUND],
    ];
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
        $moneyTransactionConvert->setStatus(self::STATUS_OPEN);

        $this->saveEntity($moneyTransactionConvert);

        return $moneyTransactionConvert;
    }

    public function getExchangeRate()
    {
        $config = require __DIR__ . '/../../config/exchangeRate.php';

        return $config['exchangeRate'];
    }

    /**
     * Запись таска на перевод денег в банк.
     *
     * @param User $user
     * @param int $sum
     * @param BankAccount $accountNumber
     *
     * @return MoneyTransactionToBank
     */
    public function openTransferTransaction(User $user, int $sum, BankAccount $accountNumber): MoneyTransactionToBank
    {
        $moneyTransactionToBank = new MoneyTransactionToBank();
        $moneyTransactionToBank->setUser($user);
        $moneyTransactionToBank->setBankAccount($accountNumber);
        $moneyTransactionToBank->setSum($sum);
        $moneyTransactionToBank->setStatus(self::STATUS_OPEN);

        $this->saveEntity($moneyTransactionToBank);

        return $moneyTransactionToBank;
    }

    /**
     * @param MoneyTransactionToBank $transactionToBank
     * @param string $newStatus
     * @throws ChangeTransactionStatusException
     */
    public function changeTransferTransactionStatus(MoneyTransactionToBank $transactionToBank, string $newStatus)
    {
        $currentStatus = $transactionToBank->getStatus();

        if (!isset($this->statusSequence[$currentStatus]) || ! in_array($newStatus, $this->statusSequence[$currentStatus])) {
            throw new ChangeTransactionStatusException("Ошибка смены статуса с $currentStatus на $newStatus в MoneyTransactionToBank id={$transactionToBank->getId()}");
        }

        $transactionToBank->setStatus($newStatus);
        $this->saveEntity($transactionToBank);
    }

    /**
     * @param MoneyTransactionConvert $transactionConvert
     * @param $newStatus
     * @throws ChangeTransactionStatusException
     */
    public function changeConvertTransactionStatus(MoneyTransactionConvert $transactionConvert,  $newStatus)
    {
        $currentStatus = $transactionConvert->getStatus();

        if (!isset($this->statusSequence[$currentStatus]) || ! in_array($newStatus, $this->statusSequence[$currentStatus])) {
            throw new ChangeTransactionStatusException("Ошибка смены статуса с $currentStatus на $newStatus в MoneyTransactionConvert id={$transactionConvert->getId()}");
        }

        $transactionConvert->setStatus($newStatus);
        $this->saveEntity($transactionConvert);
    }

    /**
     * @param $entity
     */
    private function saveEntity($entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}