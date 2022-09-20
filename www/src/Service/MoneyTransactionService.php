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
    const CONVERT_TO_POINT = 'convert';
    const TRANSFER_TO_BANK = 'transfer';

    const STATUS_OPEN = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_REFUND = 'refund';

    private $statusSequence = [
        self::STATUS_OPEN => [self::STATUS_PENDING],
        self::STATUS_PENDING => [self::STATUS_SUCCESS, self::STATUS_REFUND],
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
     */
    public function openConvertTransaction(User $user, int $sum)
    {
        $exchangeRate = $this->getExchangeRate();

        $moneyTransactionConvert = new MoneyTransactionConvert();
        $moneyTransactionConvert->setUser($user);
        $moneyTransactionConvert->setExchangeRate($exchangeRate);
        $moneyTransactionConvert->setSum($sum);
        $moneyTransactionConvert->setStatus(self::STATUS_OPEN);

        $this->saveEntity($moneyTransactionConvert);
    }

    public function getExchangeRate()
    {
        $config = require __DIR__ . '../../config/exchangeRate.php';

        return $config['exchangeRate'];
    }

    /**
     * Запись таска на перевод денег в банк.
     *
     * @param User $user
     * @param int $sum
     * @param BankAccount $accountNumber
     */
    public function openTransferTransaction(User $user, int $sum, BankAccount $accountNumber)
    {
        $moneyTransactionToBank = new MoneyTransactionToBank();
        $moneyTransactionToBank->setUser($user);
        $moneyTransactionToBank->setBankAccount($accountNumber);
        $moneyTransactionToBank->setSum($sum);
        $moneyTransactionToBank->setStatus(self::STATUS_OPEN);

        $this->saveEntity($moneyTransactionToBank);
    }

    /**
     * @param int $transactionId
     * @param string $newStatus
     * @throws ChangeTransactionStatusException
     */
    public function changeTransferTransactionStatus(int $transactionId, string $newStatus)
    {
        $moneyTransactionToBank = $this->getTransactionToBank($transactionId);
        $currentStatus = $moneyTransactionToBank->getStatus();

        if (!isset($this->statusSequence[$currentStatus]) || ! in_array($newStatus, $this->statusSequence[$currentStatus])) {
            throw new ChangeTransactionStatusException("Ошибка смены статуса с $currentStatus на $newStatus в MoneyTransactionToBank id=$transactionId");
        }

        $moneyTransactionToBank->setStatus($newStatus);
        $this->saveEntity($moneyTransactionToBank);
    }

    /**
     * @param int $transactionId
     * @param $newStatus
     * @throws ChangeTransactionStatusException
     */
    public function changeConvertTransactionStatus(int $transactionId,  $newStatus)
    {
        $moneyTransactionConvert = $this->getTransactionConvert($transactionId);
        $currentStatus = $moneyTransactionConvert->getStatus();

        if (!isset($this->statusSequence[$currentStatus]) || ! in_array($newStatus, $this->statusSequence[$currentStatus])) {
            throw new ChangeTransactionStatusException("Ошибка смены статуса с $currentStatus на $newStatus в MoneyTransactionConvert id=$transactionId");
        }

        $moneyTransactionConvert->setStatus($newStatus);
        $this->saveEntity($moneyTransactionConvert);
    }

    /**
     * @param int $transactionId
     * @return MoneyTransactionToBank|null
     */
    private function getTransactionToBank(int $transactionId): ?MoneyTransactionToBank
    {
        return $this->entityManager->find('MoneyTransactionToBank', $transactionId);
    }

    /**
     * @param int $transactionId
     * @return MoneyTransactionConvert|null
     */
    private function getTransactionConvert(int $transactionId): ?MoneyTransactionConvert
    {
        return $this->entityManager->find('MoneyTransactionConvert', $transactionId);
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