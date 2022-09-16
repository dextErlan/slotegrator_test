<?php

namespace App\Service;

interface GiftService
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