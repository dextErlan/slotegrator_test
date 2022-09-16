<?php

namespace App\Service;

class PointService implements GiftService
{
    /**
     * Бонусные баллы (случайная сумма в интервале)
     *
     * @param int $limitForMoney
     * @param int $limitForPrizes
     * @return int
     */
    public function giveaway(int $limitForMoney, int $limitForPrizes): int
    {
        $sum = $this->getSumForGiveaway();
        // TODO save to DB

        return $sum;
    }

    /**
     * Случайный выбор размера выйгрыша
     *
     * @return int
     */
    public function getSumForGiveaway(): int
    {
        return rand(1, 100);
    }
}