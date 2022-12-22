<?php

namespace Tests\Unit;

use App\Entity\User;
use App\Factory\GiftServiceFactory;
use App\Service\GiveawayService;
use App\Service\MoneyService;
use App\Service\PointService;
use App\Service\PrizeService;
use App\Service\RequestToBankAPIService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class GiftServiceFactoryTest extends TestCase
{
    public function test(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $giftServiceFactory = new GiftServiceFactory($entityManager);
        $user = new User();

        $moneyService = $giftServiceFactory->getGiftService(GiveawayService::GIFT_MONEY, $user);
        $this->assertInstanceOf(MoneyService::class, $moneyService);

        $prizeService = $giftServiceFactory->getGiftService(GiveawayService::GIFT_PRIZE, $user);
        $this->assertInstanceOf(PrizeService::class, $prizeService);

        $pointService = $giftServiceFactory->getGiftService(GiveawayService::GIFT_POINT, $user);
        $this->assertInstanceOf(PointService::class, $pointService);
    }
}
