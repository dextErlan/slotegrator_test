<?php

namespace App\Command;

use App\Entity\BankAccount;
use App\Entity\Prize;
use App\Entity\User;
use App\Entity\UserMoney;
use App\Entity\UserPoint;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FillDatabase extends Command
{
    protected static $defaultName = 'app:fill-db';
    private EntityManager $em;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Создаем пользователя
        $user = new User;
        $user->setEmail('first@user.test');
        $user->setPassword('password');
        $this->em->persist($user);

        $userMoney = new UserMoney();
        $userMoney->setUser($user);
        $userMoney->setMoneyInApp(0);
        $userMoney->setBlocked(0);
        $this->em->persist($userMoney);

        $userPoint = new UserPoint();
        $userPoint->setUser($user);
        $userPoint->setPoint(0);
        $this->em->persist($userPoint);

        // Создаем пользователю банковские счета
        $bankAccount1 = new BankAccount();
        $bankAccount1->setAccountNumber('1111-1111');
        $bankAccount1->setUser($user);
        $this->em->persist($bankAccount1);

        $bankAccount2 = new BankAccount();
        $bankAccount2->setAccountNumber('222-222');
        $bankAccount2->setUser($user);
        $this->em->persist($bankAccount2);

        // Создаем список физических призов
        $prizes = [
            "Телевизор" => 1,
            "Наушники" => 4,
            "Смартфон" => 3,
            "Планшет" => 2,
        ];
        foreach ($prizes as $prizeName => $number) {
            $prize = new Prize();
            $prize->setName($prizeName);
            $prize->setNumber($number);
            $this->em->persist($prize);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}