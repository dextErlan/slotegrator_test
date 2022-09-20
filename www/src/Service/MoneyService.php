<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Entity\UserMoney;
use App\Exception\BankAccountNotForUser;
use App\Exception\BlockedSumNotValid;
use App\Exception\FundsNotAvailableForUser;
use Doctrine\ORM\EntityManager;

class MoneyService implements GiftServiceInterface
{
    private User $user;
    private EntityManager $entityManager;
    private UserMoney $userMoney;
    private RequestToBankAPIService $bankAPIService;

    public function __construct(User $user, EntityManager $entityManager, RequestToBankAPIService $bankAPIService)
    {
        $this->user = $user;
        $this->entityManager = $entityManager;
        $this->userMoney = $this->getUserMoney();
        $this->bankAPIService = $bankAPIService;
    }

    /**
     * Денежный приз в копейках.
     * Подарок назначется пользователю.
     * Размер подарка не должен превышать переданные лимиты.
     *
     * @param int $limitForMoney
     * @param int $limitForPrizes
     * @return array
     */
    public function giveaway(int $limitForMoney, int $limitForPrizes): array
    {
        $sum = $this->getSumForGiveaway($limitForMoney);
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() + $sum);
        $this->saveUserMoney();

        return [$sum, "$sum долларов"];
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

    public function setTaskForTransferMoney(int $sum, BankAccount $accountNumber){
        if (!$this->isUserBankAccount($accountNumber)) {
            throw new BankAccountNotForUser("Банковский счет " . $accountNumber->getAccountNumber() . " не найден у пользователя с id=" . $this->user->getId());
        }
        $this->blockMoney($sum);

        $moneyTransactionService = new MoneyTransactionService($this->entityManager);
        $moneyTransactionService->openTransferTransaction($this->user, $sum, $accountNumber);
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
                'user_id' => $this->user->getId(),
                'accountNumber' => $accountNumber->getAccountNumber(),
            ]);

        return !empty($bankAccount);
    }

    public function transferMoney(MoneyTransactionToBank $transactionToBank){
        if (!$this->isUserBankAccount($accountNumber)) {
            throw new BankAccountNotForUser("Банковский счет " . $accountNumber->getAccountNumber() . " не найден у пользователя с id=" . $this->user->getId());
        }

        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше $sum у пользователя с id=" . $this->user->getId());
        }
        $this->bankAPIService->fetchTransferMoneyToBank($sum, $accountNumber->getAccountNumber());
        // unblockMoney ИЛИ refundMoney
        // MoneyTransactionService->changeTransferTransactionStatus
    }

    public function convertToUserPoints(User $user, int $sum){
        // isFundsAvailableForUser
        // setTaskForConvertToUserPoints
        // конвертация
        // PointService->addPointForUser($points)
        // MoneyTransactionService->changeConvertTransactionStatus
        // unblockMoney ИЛИ refundMoney
    }

    public function setTaskForConvertToUserPoints(User $user, int $sum){
        // blockMoney
        // MoneyTransactionService->openConvertTransaction($user, $sum).
    }

    /**
     * @param string $transactionType
     * @param int $transactionId
     * @param int $sum
     * @throws BlockedSumNotValid
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function unblockMoney(string $transactionType, int $transactionId, int $sum)
    {
        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше $sum у пользователя с id=" . $this->user->getId());
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
        $this->saveUserMoney();
    }

    //Возврат на счет пользователя, после не успешной транзакции
    private function refundMoney(){}

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
     * Проверить доступные средства.
     *
     * @param int $sum
     * @return bool
     */
    public function isFundsAvailableForUser(int $sum): bool
    {
        return $this->userMoney->getMoneyInApp() >= $sum;
    }
}