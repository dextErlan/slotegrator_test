<?php

namespace App\Service\Money;

use App\Entity\User;
use App\Entity\UserMoney;
use App\Exception\BlockedSumNotValid;
use App\Exception\FundsNotAvailableForUser;

class UserMoneyService
{
    private User $user;
    private UserMoney $userMoney;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->userMoney = $this->getUserMoney();
    }

    /**
     * @return UserMoney
     */
    public function getUserMoney(): UserMoney
    {
        if (!empty($this->userMoney)) {
            return $this->userMoney;
        }

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
     * @param int $sum
     */
    public function addMoney(int $sum): void
    {
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() + $sum);
    }

    /**
     * @param int $sum
     * @throws BlockedSumNotValid
     */
    public function withdrawalBlockMoney(int $sum): void
    {
        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше чем $sum у пользователя с email=" . $this->user->getEmail());
        }

        $this->userMoney->setBlocked($this->userMoney->getBlocked() - $sum);
    }

    /**
     * @param int $sum
     * @throws FundsNotAvailableForUser
     */
    public function blockMoney(int $sum): void
    {
        if (! $this->userMoney->isFundsAvailableForUser($sum)) {
            throw new FundsNotAvailableForUser("Не возможно заблокировать сумму $sum из-за недостаточности средств у пользователя с email=" . $this->user->getEmail());
        }

        $this->userMoney->setBlocked($this->userMoney->getBlocked() + $sum);
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() - $sum);
    }

    /**
     * @param int $sum
     * @throws BlockedSumNotValid
     */
    public function refundMoney(int $sum): void
    {
        if ($this->userMoney->getBlocked() < $sum) {
            throw new BlockedSumNotValid("Заблокированная сумма меньше чем $sum у пользователя с email=" . $this->user->getEmail());
        }

        $this->userMoney->setBlocked($this->userMoney->getBlocked() - $sum);
        $this->userMoney->setMoneyInApp($this->userMoney->getMoneyInApp() + $sum);
    }
}