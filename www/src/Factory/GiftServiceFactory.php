<?php

namespace App\Factory;

use App\Entity\User;
use App\Service\GiftServiceInterface;
use App\Service\GiveawayService;
use App\Service\MoneyService;
use App\Service\PointService;
use App\Service\PrizeService;
use App\Service\Money\UserMoneyService;
use Doctrine\ORM\EntityManagerInterface;

class GiftServiceFactory
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getGiftService(string $giftType, User $user): GiftServiceInterface
    {
        if (GiveawayService::GIFT_MONEY === $giftType) {
            return new MoneyService($user, (new UserMoneyService($user)), $this->entityManager);
        }

        if (GiveawayService::GIFT_PRIZE === $giftType) {
            return new PrizeService($user, $this->entityManager);
        }

        return new PointService($user, $this->entityManager);
    }
}