<?php

namespace App\Service;

interface GiftServiceInterface
{
    /**
     * Подарок назначется пользователю.
     * Размер подарка не должен превышать переданные лимиты.
     *
     * @param int $limitForGifts
     * @return array
     */
    public function giveaway(int $limitForGifts): array;
}