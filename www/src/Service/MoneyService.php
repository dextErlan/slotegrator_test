<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionConvert;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Exception\BankAccountNotForUser;
use App\Exception\BankAPIException;
use App\Exception\BlockedSumNotValid;
use App\Exception\FundsNotAvailableForUser;
use App\Exception\TransferException;
use App\Service\Money\MoneyTransactionService;
use App\Service\Money\UserMoneyService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class MoneyService implements GiftServiceInterface
{
    private User $user;
    private UserMoneyService $userMoneyService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        User $user,
        UserMoneyService $userMoneyService,
        EntityManagerInterface $entityManager
    ) {
        $this->user = $user;
        $this->userMoneyService = $userMoneyService;
        $this->entityManager = $entityManager;
    }

    /**
     * Денежный приз в копейках.
     * Подарок назначется пользователю.
     * Размер подарка не должен превышать переданные лимиты.
     *
     * @param int $limitForGifts
     * @return array
     */
    public function giveaway(int $limitForGifts): array
    {
        $sum = $this->getSumForGiveaway($limitForGifts);
        $this->userMoneyService->addMoney($sum);
        $this->saveEntity($this->userMoneyService->getUserMoney());

        return [$sum, "$sum долларов"];
    }

    /**
     * Поставить задачу на перевод денег в Банк.
     *
     * @param int $sum
     * @param BankAccount $bankAccount
     * @return MoneyTransactionToBank
     * @throws BankAccountNotForUser
     * @throws FundsNotAvailableForUser
     */
    public function setTaskForTransferMoney(int $sum, BankAccount $bankAccount): MoneyTransactionToBank
    {
        $moneyTransactionService = new MoneyTransactionService();
        $moneyTransactionToBank = $moneyTransactionService->openTransferTransaction($this->user, $sum, $bankAccount);
        $this->saveEntity($moneyTransactionToBank);

        $this->userMoneyService->blockMoney($sum);
        $this->saveEntity($this->userMoneyService->getUserMoney());

        return $moneyTransactionToBank;
    }

    /**
     * Перевод денег в Банк.
     *
     * @param MoneyTransactionToBank $transactionToBank
     * @param RequestToBankAPIServiceInterface $bankAPIService
     * @throws BankAccountNotForUser
     * @throws BlockedSumNotValid
     * @throws TransferException
     * @throws \App\Exception\ChangeTransactionStatusException
     */
    public function transferMoney(
        MoneyTransactionToBank $transactionToBank,
        RequestToBankAPIServiceInterface $bankAPIService
    ) {
        $bankAccount = $transactionToBank->getBankAccount();
        $sum = $transactionToBank->getSum();
        $userMoney = $this->userMoneyService->getUserMoney();

        if (! $bankAccount->isBelongsToUser($this->user)) {
            throw new BankAccountNotForUser(
                sprintf(
                    "Банковский счет %s  не найден у пользователя с email = %s",
                    $bankAccount->getAccountNumber(),
                    $this->user->getEmail()
                )
            );
        }

        if ($userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid(
                sprintf(
                    "Заблокированная сумма меньше %d у пользователя с email=%s",
                    $sum,
                    $this->user->getEmail()
                )
            );
        }

        $transactionService = new MoneyTransactionService();

        try {
            $bankAPIService->fetchTransferMoneyToBank($sum, $bankAccount->getAccountNumber());
        } catch (BankAPIException $e) {
            $this->userMoneyService->refundMoney($sum);
            $this->saveEntity($this->userMoneyService->getUserMoney());

            $transactionService->changeTransferTransactionStatus(
                $transactionToBank,
                MoneyTransactionToBank::STATUS_REFUND
            );
            $this->saveEntity($transactionToBank);

            throw new TransferException("Перевод денег отменен");
        }

        $this->userMoneyService->withdrawalBlockMoney($sum);
        $this->saveEntity($this->userMoneyService->getUserMoney());

        $transactionService->changeTransferTransactionStatus(
            $transactionToBank,
            MoneyTransactionToBank::STATUS_SUCCESS
        );
        $this->saveEntity($transactionToBank);
    }

    /**
     * @param int $sum
     * @param PointServiceInterface $pointService
     * @throws BlockedSumNotValid
     * @throws FundsNotAvailableForUser
     * @throws TransferException
     * @throws \App\Exception\ChangeTransactionStatusException
     */
    public function convertToUserPoints(int $sum, PointServiceInterface $pointService){
        $this->userMoneyService->blockMoney($sum);
        $this->saveEntity($this->userMoneyService->getUserMoney());

        $moneyTransactionService = new MoneyTransactionService();
        $moneyTransactionConvert = $moneyTransactionService->openConvertTransaction($this->user, $sum);
        $this->saveEntity($moneyTransactionConvert);

        $points = intval($moneyTransactionConvert->getExchangeRate() * $sum);

        try {
            $pointService->addPoint($points);
        }catch (Exception $e) {
            $this->userMoneyService->refundMoney($sum);
            $this->saveEntity($this->userMoneyService->getUserMoney());

            $moneyTransactionService->changeConvertTransactionStatus(
                $moneyTransactionConvert,
                MoneyTransactionConvert::STATUS_REFUND
            );
            $this->saveEntity($moneyTransactionConvert);

            throw new TransferException("Конверация денег отменена");
        }

        $moneyTransactionService->changeConvertTransactionStatus(
            $moneyTransactionConvert,
            MoneyTransactionConvert::STATUS_SUCCESS
        );
        $this->saveEntity($moneyTransactionConvert);

        $this->userMoneyService->withdrawalBlockMoney($sum);
        $this->saveEntity($this->userMoneyService->getUserMoney());
    }

    /**
     * Случайный выбор размера выйгрыша, от 1 до переданного лимита
     *
     * @param int $limitForMoney
     * @return int
     */
    private function getSumForGiveaway(int $limitForMoney): int
    {
        if ($limitForMoney > 0) {
            return rand(1, $limitForMoney);
        }

        return 0;
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