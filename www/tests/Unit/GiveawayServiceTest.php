<?php

namespace Tests\Unit;

use App\Service\GiveawayService;
use PHPUnit\Framework\TestCase;

class GiveawayServiceTest extends TestCase
{
    public function testSetLimitForMoneys()
    {
        $newLimitForMoney = 25;

        $giveawayService = new GiveawayService();
        $giveawayService->setLimitForMoney($newLimitForMoney);

        self::assertEquals($newLimitForMoney, GiveawayService::getLimitForMoney());
    }

    public function testSetLimitForPrizes()
    {
        $newLimitForPrizes = 25;

        $giveawayService = new GiveawayService();
        $giveawayService->setLimitForPrizes($newLimitForPrizes);

        self::assertEquals($newLimitForPrizes, GiveawayService::getLimitForPrizes());
    }
}