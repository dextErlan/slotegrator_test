<?php

namespace App\Service;

use App\Entity\User;
use App\Factory\GiftServiceFactory;

class GiveawayService
{
    const GIFT_MONEY = 'Money';
    const GIFT_PRIZE = 'Prize';
    const GIFT_POINT = 'Point';

    private static int $limitForMoney = 100;
    private static int $limitForPrizes = 10; // TODO синхронизация с кол-вом в Prize
    private GiftServiceFactory $giftServiceFactory;

    public function __construct(GiftServiceFactory $giftServiceFactory)
    {
        $this->giftServiceFactory = $giftServiceFactory;
    }

    /**
     * Выбрать случайный подарок из доступных.
     * Присвоить его пользователю.
     * Уменьшить лимит подарков
     *
     * @param User $user
     *
     * @return string
     */
    public function getRandomGift(User $user): string
    {
        $giftList = $this->getGiftList();
        $randomGift = $giftList[array_rand($giftList)];
        $giftService = $this->giftServiceFactory->getGiftService($randomGift, $user);
        list($giftCount, $giftDescription) = $giftService->giveaway(self::$limitForMoney);
        $this->decreaseLimit($randomGift, $giftCount);

        return $giftDescription;
    }

    /**
     * Получить список доступных подарков для розыгрыша.
     *
     * @return string[]
     */
    private function getGiftList(): array
    {
        $giftList = [self::GIFT_POINT];

        if (self::$limitForMoney > 0) {
            $giftList[] = self::GIFT_MONEY;
        }

        if (self::$limitForPrizes > 0) {
            $giftList[] = self::GIFT_PRIZE;
        }

        return $giftList;
    }

    /**
     * Уменьшаем лимит подарков
     *
     * @param string $giftType
     * @param int $giftCount
     * @return void
     */
    private function decreaseLimit(string $giftType, int $giftCount): void
    {
        switch ($giftType) {
            case self::GIFT_MONEY:
                $this->setLimitForMoney(self::$limitForMoney - $giftCount);
                break;
            case self::GIFT_PRIZE:
                $this->setLimitForPrizes(self::$limitForPrizes - $giftCount);
                break;
        }
    }

    /**
     * @param int $limitForMoney
     */
    public function setLimitForMoney(int $limitForMoney): void
    {
        self::$limitForMoney = $limitForMoney;
    }

    /**
     * @param int $limitForPrizes
     */
    public function setLimitForPrizes(int $limitForPrizes): void
    {
        self::$limitForPrizes = $limitForPrizes;
    }

    /**
     * @return int
     */
    static function getLimitForMoney(): int
    {
        return self::$limitForMoney;
    }

    /**
     * @return int
     */
    static function getLimitForPrizes(): int
    {
        return self::$limitForPrizes;
    }
}