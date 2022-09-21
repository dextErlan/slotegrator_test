<?php

namespace App\Repository;

use App\Entity\Prize;
use Doctrine\ORM\EntityRepository;

class PrizeRepository extends EntityRepository
{
    /**
     * @return int|mixed|string
     */
    public function getAvailablePrizes()
    {
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder()
            ->select('p')
            ->from(Prize::class, 'p')
            ->where('p.number > :number')
            ->setParameter('number', 0)
            ->getQuery();

        return $query->execute();
    }
}