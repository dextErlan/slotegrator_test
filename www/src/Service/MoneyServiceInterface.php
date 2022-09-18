<?php

namespace App\Service;

use App\Entity\BankAccount;
use App\Entity\UserMoney;
use App\Exception\BankAccountNotForUser;
use App\Exception\FundsNotAvailableForUser;
use Doctrine\ORM\EntityManager;

class MoneyServiceInterface implements GiftServiceInterface
{
    private UserMoney $userMoney;
    private EntityManager $entityManager;

    public function __construct(UserMoney $userMoney, EntityManager $entityManager)
    {
        $this->userMoney = $userMoney;
        $this->entityManager = $entityManager;
    }

    /**
     * Денежный приз в копейках.
     * Подарок назначется пользователю.
     * Размер подарка не должен превышать переданные лимиты.
     *
     * @param int $limitForMoney
     * @param int $limitForPrizes
     * @return int
     */
    public function giveaway(int $limitForMoney, int $limitForPrizes): int
    {
        $sum = $this->getSumForGiveaway($limitForMoney);
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() + $sum);
        $this->saveUserMoney();

        return $sum;
    }

    private function saveUserMoney()
    {
        $this->entityManager->persist($this->userMoney);
        $this->entityManager->flush();
    }

    public function setTaskForTransferMoney(int $sum, BankAccount $accountNumber){
        if (!$this->isUserBankAccount($accountNumber)) {
            throw new BankAccountNotForUser("Банковский счет " . $accountNumber->getAccountNumber() . " не найден у пользователя с id=" . $this->userMoney->getUser()->getId());
        }
        $this->blockMoney($sum);
        // MoneyTransactionService->openTransferTransaction()
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
                'user_id' => $this->userMoney->getUser()->getId(),
                'accountNumber' => $accountNumber->getAccountNumber(),
            ]);

        return !empty($bankAccount);
    }

    public function transferMoney(){
        // Поиск записей в MoneyTransactionToBank со статусом и в цикле по каждой записи
        // isUserBankAccount
        // isFundsAvailableForUser
        // Обращение к апи банка и передача DTO
        // unblockMoney ИЛИ refundMoney
        // MoneyTransactionService->changeTransferTransactionStatus
    }

    public function convertToUserPoints(User $user, int $sum){
        // isFundsAvailableForUser
        // setTaskForConvertToUserPoints
        // конвертация
        // PointServiceInterface->addPointForUser($points)
        // MoneyTransactionService->changeConvertTransactionStatus
        // unblockMoney ИЛИ refundMoney
    }

    public function setTaskForConvertToUserPoints(User $user, int $sum){
        // blockMoney
        // MoneyTransactionService->openConvertTransaction($user, $sum).
    }

    //снятие денег с блока
    public function unblockMoney(){}

    //Блок переводимой суммы
    private function blockMoney(int $sum)
    {
        if (!$this->isFundsAvailableForUser($sum)) {
            throw new FundsNotAvailableForUser("Не возможно заблокировать сумму $sum из-за недостаточности средств у пользователя с id=" . $this->userMoney->getUser()->getId());
        }

        $this->userMoney->setBlocked($sum);
        $this->saveUserMoney();
    }

    //Возврат на счет пользователя, после не успешной транзакции
    public function refundMoney(){}

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