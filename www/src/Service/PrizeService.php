<?php

namespace App\Service;

class PrizeService implements GiftServiceInterface
{
    /**
     * Физический предмет (случайный предмет из списка)
     *
     * @param int $limitForMoney
     * @param int $limitForPrizes
     * @return int
     */
    public function giveaway(int $limitForMoney, int $limitForPrizes): int
    {
        // TODO: Implement giveaway() method.
        return 1;
    }
}