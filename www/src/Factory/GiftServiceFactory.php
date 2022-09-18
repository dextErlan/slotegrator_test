<?php

namespace App\Factory;

use App\Service\GiftServiceInterface;
use App\Service\GiveawayService;
use App\Service\MoneyServiceInterface;
use App\Service\PointServiceInterface;
use App\Service\PrizeServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Authentication\AuthenticationServiceInterface;

class GiftServiceFactory
{
    private EntityManagerInterface $entityManager;
    private AuthenticationServiceInterface $authService;

    public function __construct(EntityManagerInterface $entityManager, AuthenticationServiceInterface $authService)
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
    }

    public function getGiftService(string $giftType): GiftServiceInterface
    {
        $user = $this->authService->getIdentity();

        if (GiveawayService::GIFT_MONEY === $giftType) {
            return new MoneyServiceInterface();
        }

        if (GiveawayService::GIFT_PRIZE === $giftType) {
            return new PrizeServiceInterface();
        }

        return new PointServiceInterface();
    }
}