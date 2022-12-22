<?php

namespace App\Service;

use App\Entity\Prize;
use App\Entity\User;
use App\Entity\UserPrize;
use App\Exception\AvailablePrizesEmptyException;
use App\Exception\LimitForPrizesEndException;
use App\Repository\PrizeRepository;
use Doctrine\ORM\EntityManagerInterface;

class PrizeService implements GiftServiceInterface
{
    private User $user;
    private EntityManagerInterface $entityManager;

    public function __construct(User $user, EntityManagerInterface $entityManager)
    {
        $this->user = $user;
        $this->entityManager = $entityManager;
    }

    /**
     * Физический предмет (случайный предмет из списка)
     *
     * @param int $limitForGifts
     * @return array{int, string}
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function giveaway(int $limitForGifts): array
    {
        $prize = $this->getPrizeForGiveaway($limitForGifts);

        $userPrize = new UserPrize();
        $userPrize->setUser($this->user);
        $userPrize->setStatus(UserPrize::STATUS_WIN);
        $userPrize->setPrize($prize);

        $this->saveEntity($userPrize);

        $this->decreaseNumberForPrize($prize);

        return [1, $prize->getName()];
    }

    /**
     * @param UserPrize|Prize $entity
     */
    private function saveEntity(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Случайный предмет из списка.
     *
     * @param int $limitForPrizes
     * @return Prize
     * @throws AvailablePrizesEmptyException
     */
    private function getPrizeForGiveaway(int $limitForPrizes): Prize
    {
        if ($limitForPrizes <= 0) {
            throw new LimitForPrizesEndException("Лимит на призы исчерпан!");
        }

        /** @var prizeRepository PrizeRepository */
        $prizeRepository = $this->entityManager->getRepository(Prize::class);
        $prizes = $prizeRepository->getAvailablePrizes();

        if (empty($prizes)) {
            throw new AvailablePrizesEmptyException('Не подарков для розыгрыша!');
        }

        $randIndex = rand(0, count($prizes)-1);

        return $prizes[$randIndex];
    }

    /**
     * @param Prize $prize
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function decreaseNumberForPrize(Prize $prize): void
    {
        $prize->setNumber($prize->getNumber() - 1);
        $this->saveEntity($prize);
    }
}