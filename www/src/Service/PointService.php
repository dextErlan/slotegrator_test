<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserPoint;
use Doctrine\ORM\EntityManagerInterface;

class PointService implements GiftServiceInterface, PointServiceInterface
{
    private User $user;
    private EntityManagerInterface $entityManager;
    private UserPoint $userPoint;

    public function __construct(User $user, EntityManagerInterface $entityManager)
    {
        $this->user = $user;
        $this->entityManager = $entityManager;
        $this->userPoint = $this->getUserPoint();
    }

    /**
     * Бонусные баллы (случайная сумма в интервале)
     *
     * @param int $limitForGifts
     * @return array
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function giveaway(int $limitForGifts): array
    {
        $sum = $this->getSumForGiveaway();
        $this->addPoint($sum);

        return [$sum, "$sum баллов"];
    }

    /**
     * Добавляем бонусные баллы пользователю.
     *
     * @param $sum
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addPoint($sum)
    {
        $this->userPoint->setPoint($this->userPoint->getPoint() + $sum);
        $this->saveUserPoint();
    }

    /**
     * @return UserPoint
     */
    private function getUserPoint(): UserPoint
    {
        $userPoint = $this->user->getUserPoint();

        if (empty($userPoint)) {
            $userPoint = new UserPoint();
            $userPoint->setUser($this->user);
            $userPoint->setPoint(0);
        }

        return $userPoint;
    }

    /**
     * @throws \Doctrine\ORM\Exception\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function saveUserPoint()
    {
        $this->entityManager->persist($this->userPoint);
        $this->entityManager->flush();
    }

    /**
     * Случайный выбор размера выйгрыша
     *
     * @return int
     */
    private function getSumForGiveaway(): int
    {
        return rand(1, 100);
    }
}