<?php

namespace App\Service;

interface GiftServiceInterface
{
    /**
     * Подарок назначется пользователю.
     * Размер подарка не должен превышать переданные лимиты.
     *
     * @param int $limitForMoney
     * @param int $limitForPrizes
     * @return int
     */
    public function giveaway(int $limitForMoney, int $limitForPrizes): int;
}