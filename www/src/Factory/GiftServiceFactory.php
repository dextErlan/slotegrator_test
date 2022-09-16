<?php

namespace App\Factory;

use App\Service\GiftService;
use App\Service\GiveawayService;
use App\Service\MoneyService;
use App\Service\PointService;
use App\Service\PrizeService;

class GiftServiceFactory
{
    public static function getGiftService(string $giftType): GiftService
    {
        if (GiveawayService::GIFT_MONEY === $giftType) {
            return new MoneyService();
        }

        if (GiveawayService::GIFT_PRIZE === $giftType) {
            return new PrizeService();
        }

        return new PointService();
    }
}