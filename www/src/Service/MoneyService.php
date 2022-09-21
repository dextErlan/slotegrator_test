<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Entity\UserMoney;
use App\Exception\BankAccountNotForUser;
use App\Exception\BankAPIException;
use App\Exception\BlockedSumNotValid;
use App\Exception\FundsNotAvailableForUser;
use App\Exception\TransferException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class MoneyService implements GiftServiceInterface
{
    private User $user;
    private EntityManagerInterface $entityManager;
    private UserMoney $userMoney;

    public function __construct(
        User $user,
        EntityManagerInterface $entityManager
    ) {
        $this->user = $user;
        $this->entityManager = $entityManager;
        $this->userMoney = $this->getUserMoney();
    }

    /**
     * Денежный приз в копейках.
     * Подарок назначется пользователю.
     * Размер подарка не должен превышать переданные лимиты.
     *
     * @param int $limitForGifts
     * @return array
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function giveaway(int $limitForGifts): array
    {
        $sum = $this->getSumForGiveaway($limitForGifts);
        $this->addMoney($sum);

        return [$sum, "$sum долларов"];
    }

    /**
     * @param int $sum
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addMoney(int $sum)
    {
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() + $sum);
        $this->saveUserMoney();
    }

    /**
     * Поставить задачу на перевод денег в Банк.
     *
     * @param int $sum
     * @param BankAccount $accountNumber
     * @return MoneyTransactionToBank
     * @throws BankAccountNotForUser
     * @throws FundsNotAvailableForUser
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setTaskForTransferMoney(int $sum, BankAccount $accountNumber): MoneyTransactionToBank
    {
        if (!$this->isUserBankAccount($accountNumber)) {
            throw new BankAccountNotForUser("Банковский счет " . $accountNumber->getAccountNumber() . " не найден у пользователя с id=" . $this->user->getId());
        }
        $this->blockMoney($sum);

        $moneyTransactionService = new MoneyTransactionService($this->entityManager);

        return $moneyTransactionService->openTransferTransaction($this->user, $sum, $accountNumber);
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
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function transferMoney(MoneyTransactionToBank $transactionToBank, RequestToBankAPIServiceInterface $bankAPIService){
        $accountNumber = $transactionToBank->getBankAccount();
        $sum = $transactionToBank->getSum();

        if (!$this->isUserBankAccount($accountNumber)) {
            throw new BankAccountNotForUser("Банковский счет " . $accountNumber->getAccountNumber() . " не найден у пользователя с id=" . $this->user->getId());
        }

        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше $sum у пользователя с id=" . $this->user->getId());
        }

        $transactionService = new MoneyTransactionService($this->entityManager);

        try {
            $bankAPIService->fetchTransferMoneyToBank($sum, $accountNumber->getAccountNumber());
        } catch (BankAPIException $e) {
            $this->refundMoney($sum);
            $transactionService->changeTransferTransactionStatus($transactionToBank, MoneyTransactionService::STATUS_REFUND);

            throw new TransferException("Перевод денег отменен");
        }

        $this->unblockMoney($sum);

        $transactionService->changeTransferTransactionStatus($transactionToBank, MoneyTransactionService::STATUS_SUCCESS);
    }

    /**
     * @param int $sum
     * @throws BlockedSumNotValid
     * @throws FundsNotAvailableForUser
     * @throws TransferException
     * @throws \App\Exception\ChangeTransactionStatusException
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function convertToUserPoints(int $sum, PointServiceInterface $pointService){
        $this->blockMoney($sum);

        $moneyTransactionService = new MoneyTransactionService($this->entityManager);
        $moneyTransactionConvert = $moneyTransactionService->openConvertTransaction($this->user, $sum);

        $points = intval($moneyTransactionConvert->getExchangeRate() * $sum);

        try {
            $pointService->addPoint($points);
        }catch (Exception $e) {
            $this->refundMoney($sum);
            $moneyTransactionService->changeConvertTransactionStatus($moneyTransactionConvert, MoneyTransactionService::STATUS_REFUND);

            throw new TransferException("Конверация денег отменена");
        }

        $moneyTransactionService->changeConvertTransactionStatus($moneyTransactionConvert, MoneyTransactionService::STATUS_SUCCESS);
        $this->unblockMoney($sum);
    }

    /**
     * Проверить доступные средства.
     *
     * @param int $sum
     * @return bool
     */
    public function isFundsAvailableForUser(int $sum): bool
    {
        return $this->userMoney->getMoneyInApp() >= $sum;
    }

    /**
     * @return UserMoney
     */
    private function getUserMoney(): UserMoney
    {
        $userMoney = $this->user->getUserMoney();

        if (empty($userMoney)) {
            $userMoney = new UserMoney();
            $userMoney->setUser($this->user);
            $userMoney->setMoneyInApp(0);
            $userMoney->setBlocked(0);
        }

        return $userMoney;
    }

    /**
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveUserMoney()
    {
        $this->entityManager->persist($this->userMoney);
        $this->entityManager->flush();
    }

    /**
     * Проверка принадлежит ли счет пользователю.
     *
     * @param BankAccount $accountNumber
     * @return bool
     */
    private function isUserBankAccount(BankAccount $accountNumber): bool
    {
        $bankAccount = $this->entityManager
            ->getRepository(BankAccount::class)
            ->findOneBy([
                'user' => $this->user,
                'accountNumber' => $accountNumber->getAccountNumber(),
            ]);

        return !empty($bankAccount);
    }

    /**
     * @param int $sum
     * @throws BlockedSumNotValid
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function unblockMoney(int $sum)
    {
        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше чем $sum у пользователя с id=" . $this->user->getId());
        }

        $this->userMoney->setBlocked($this->userMoney->getBlocked() - $sum);
        $this->saveUserMoney();
    }

    /**
     * @param int $sum
     * @throws FundsNotAvailableForUser
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function blockMoney(int $sum)
    {
        if (! $this->isFundsAvailableForUser($sum)) {
            throw new FundsNotAvailableForUser("Не возможно заблокировать сумму $sum из-за недостаточности средств у пользователя с id=" . $this->user->getId());
        }

        $this->userMoney->setBlocked($sum);
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() - $sum);
        $this->saveUserMoney();
    }

    /**
     * @param int $sum
     * @throws BlockedSumNotValid
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function refundMoney(int $sum)
    {
        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше чем $sum у пользователя с id=" . $this->user->getId());
        }

        $this->userMoney->setBlocked($this->userMoney->getBlocked() - $sum);
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() + $sum);
        $this->saveUserMoney();
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
}