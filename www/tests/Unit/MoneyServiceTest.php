<?php

namespace Tests\Unit;

use App\Entity\UserMoney;
use App\Service\MoneyService;
use Laminas\Http\Client\Adapter\Test;

class MoneyServiceTest extends Test
{
    public function testGiveaway()
    {
        $userMoney = new UserMoney();
        $userMoney->setMoneyInApp(10);
        $userMoney->setBlocked(0);

        $moneyService = new MoneyService($userMoney);
    }
}