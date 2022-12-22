<?php

namespace App\Command;

use App\Entity\MoneyTransactionToBank;
use App\Service\Money\UserMoneyService;
use App\Service\MoneyService;
use App\Service\RequestToBankAPIService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMoneyToBank extends Command
{
    protected static $defaultName = 'app:send-money-to-bank';
    private EntityManager $em;
    private RequestToBankAPIService $bankAPIService;

    public function __construct(EntityManager $entityManager, RequestToBankAPIService $bankAPIService)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->bankAPIService = $bankAPIService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $transactions = $this->em->getRepository(MoneyTransactionToBank::class)
            ->findBy(['status' => MoneyTransactionToBank::STATUS_OPEN]);

        foreach ($transactions as $transaction) {
            $user = $transaction->getUser();
            $moneyService = new MoneyService($user, new UserMoneyService($user), $this->em);
            $moneyService->transferMoney($transaction, $this->bankAPIService);
        }

        return Command::SUCCESS;
    }
}