<?php

namespace Tests\Feature\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Exception\BankAccountNotForUser;
use App\Exception\BankAPIException;
use App\Exception\FundsNotAvailableForUser;
use App\Exception\TransferException;
use App\Service\Money\UserMoneyService;
use App\Service\MoneyService;
use App\Service\PointService;
use App\Service\RequestToBankAPIServiceInterface;
use Exception;
use Tests\TestCase;

class MoneyServiceTest extends TestCase
{
    private User $user;
    private MoneyService $moneyService;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'first@user.test']);

        if (!$user) {
            throw new \Exception('User first@user.test not found for test!');
        }
        $this->user = $user;
        $userMoneyService = new UserMoneyService($this->user);
        $this->moneyService = new MoneyService($this->user, $userMoneyService, $em);
        $userMoneyService->addMoney(100);
    }

    public function testGiveawayZeroWhenLimitEnd(): void
    {
        $limitForGifts = [0, -1];

        foreach ($limitForGifts as $limit) {
            list($giftCount, $giftDescription) = $this->moneyService->giveaway($limit);

            $this->assertEquals(0, $giftCount);
            $this->assertEquals("0 долларов", $giftDescription);
        }
    }

    public function testGiveawayMoney(): void
    {
        $limitForGifts = 54;
        $userMoney = $this->user->getUserMoney();
        $moneyInApp = is_null($userMoney) ? 0 : $userMoney->getMoneyInApp();

        list($giftCount, $giftDescription) = $this->moneyService->giveaway($limitForGifts);

        $this->assertGreaterThan(0, $giftCount);
        $this->assertLessThanOrEqual($limitForGifts, $giftCount);

        $userMoney2 = $this->user->getUserMoney();
        $moneyInApp2 = is_null($userMoney2) ? 0 : $userMoney2->getMoneyInApp();
        $this->assertEquals($moneyInApp + $giftCount, $moneyInApp2);
    }

    public function testOpenTransferWithWrongBankAccount(): void
    {
        $sum = 10;
        $user2 = new User();
        $user2->setEmail('zx2@site.com');
        $bankAccount = new BankAccount();
        $bankAccount->setAccountNumber('444');
        $bankAccount->setUser($user2);

        $this->expectException(BankAccountNotForUser::class);

        $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);
    }

    public function testOpenTransferWhenMoneyNotEnough(): void
    {
        $userMoney = $this->user->getUserMoney();
        $moneyInApp = is_null($userMoney) ? 0 : $userMoney->getMoneyInApp();
        $sum = $moneyInApp + 10;
        $bankAccount = ($this->user->getBankAccount())[0];

        $this->expectException(FundsNotAvailableForUser::class);

        $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);
    }

    public function testOpenTransferSuccessAndRefund(): void
    {
        $userMoney = $this->user->getUserMoney();
        $moneyInApp = is_null($userMoney) ? 0 : $userMoney->getMoneyInApp();
        $blocked = is_null($userMoney) ? 0 : $userMoney->getBlocked();
        $sum = 10;
        $bankAccount = ($this->user->getBankAccount())[0];

        $transaction = $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);

        // Проверяем что деньги заблокировались и создалась запись в транзакциях
        $userMoney2 = $this->user->getUserMoney();
        $moneyInApp2 = is_null($userMoney2) ? 0 : $userMoney2->getMoneyInApp();
        $this->assertEquals($moneyInApp - $sum, $moneyInApp2);
        $blocked2 = is_null($userMoney2) ? 0 : $userMoney2->getBlocked();
        $this->assertEquals($blocked + $sum, $blocked2);
        $this->assertInstanceOf(MoneyTransactionToBank::class, $transaction);
        $this->assertEquals($sum, $transaction->getSum());
        $this->assertEquals(MoneyTransactionToBank::STATUS_OPEN, $transaction->getStatus());

        $bankAPIService = $this->createMock(RequestToBankAPIServiceInterface::class);
        $bankAPIService->method('fetchTransferMoneyToBank')
            ->willThrowException(new BankAPIException);

        try{
            $this->moneyService->transferMoney($transaction, $bankAPIService);
        } catch(Exception $e){
            // Проверяем что деньги вернулись
            $userMoney3 = $this->user->getUserMoney();
            $moneyInApp3 = is_null($userMoney3) ? 0 : $userMoney3->getMoneyInApp();
            $this->assertEquals($moneyInApp, $moneyInApp3);
            $blocked3 = is_null($userMoney3) ? 0 : $userMoney3->getBlocked();
            $this->assertEquals($blocked, $blocked3);
            $this->assertEquals(MoneyTransactionToBank::STATUS_REFUND, $transaction->getStatus());
            $this->assertInstanceOf(TransferException::class, $e);
        }
    }

    public function testTransferSuccess(): void
    {
        $userMoney = $this->user->getUserMoney();
        $moneyInApp = is_null($userMoney) ? 0 : $userMoney->getMoneyInApp();
        $blocked = is_null($userMoney) ? 0 : $userMoney->getBlocked();
        $sum = 10;
        $bankAccount = ($this->user->getBankAccount())[0];

        $transaction = $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);

        // Проверяем что деньги заблокировались и создалась запись в транзакциях
        $userMoney2 = $this->user->getUserMoney();
        $moneyInApp2 = is_null($userMoney2) ? 0 : $userMoney2->getMoneyInApp();
        $this->assertEquals($moneyInApp - $sum, $moneyInApp2);
        $blocked2 = is_null($userMoney2) ? 0 : $userMoney2->getBlocked();
        $this->assertEquals($blocked + $sum, $blocked2);
        $this->assertInstanceOf(MoneyTransactionToBank::class, $transaction);
        $this->assertEquals($sum, $transaction->getSum());
        $this->assertEquals(MoneyTransactionToBank::STATUS_OPEN, $transaction->getStatus());

        $bankAPIService = $this->createMock(RequestToBankAPIServiceInterface::class);
        $bankAPIService->method('fetchTransferMoneyToBank')
            ->willReturn([200, ['msg' => 'success']]);

        $this->moneyService->transferMoney($transaction, $bankAPIService);

        // Проверяем что списались
        $userMoney3 = $this->user->getUserMoney();
        $moneyInApp3 = is_null($userMoney3) ? 0 : $userMoney3->getMoneyInApp();
        $this->assertEquals($moneyInApp - $sum, $moneyInApp3);
        $blocked3 = is_null($userMoney3) ? 0 : $userMoney3->getBlocked();
        $this->assertEquals($blocked, $blocked3);
        $this->assertEquals(MoneyTransactionToBank::STATUS_SUCCESS, $transaction->getStatus());
    }

    public function testTransferWithWrongBankAccount(): void
    {
        // TODO
        $this->assertTrue(true);
    }

    public function testTransferWhenMoneyNotEnough(): void
    {
        // TODO
        $this->assertTrue(true);
    }

    public function testOpenConvertSuccessAndRefund(): void
    {
        $userMoney = $this->user->getUserMoney();
        $moneyInApp = is_null($userMoney) ? 0 : $userMoney->getMoneyInApp();
        $sum = $moneyInApp;
        $blocked = is_null($userMoney) ? 0 : $userMoney->getBlocked();

        $pointService = $this->createMock(PointService::class);
        $pointService->method('addPoint')
            ->willThrowException(new Exception);

        try {
            $this->moneyService->convertToUserPoints($sum, $pointService);
        } catch(Exception $e){
            // Проверяем что деньги остались в прежнем состоянии
            $userMoney2 = $this->user->getUserMoney();
            $moneyInApp2 = is_null($userMoney2) ? 0 : $userMoney2->getMoneyInApp();
            $this->assertEquals($moneyInApp, $moneyInApp2);
            $blocked2 = is_null($userMoney2) ? 0 : $userMoney2->getBlocked();
            $this->assertEquals($blocked, $blocked2);
            $this->assertInstanceOf(TransferException::class, $e);
        }
    }

    public function testConvertSuccess(): void
    {
        $userMoney = $this->user->getUserMoney();
        $moneyInApp = is_null($userMoney) ? 0 : $userMoney->getMoneyInApp();
        $blocked = is_null($userMoney) ? 0 : $userMoney->getBlocked();
        $sum = 15;

        $pointService = $this->createMock(PointService::class);

        $this->moneyService->convertToUserPoints($sum, $pointService);

        // Проверяем что списались
        $userMoney2 = $this->user->getUserMoney();
        $moneyInApp2 = is_null($userMoney2) ? 0 : $userMoney2->getMoneyInApp();
        $this->assertEquals($moneyInApp - $sum, $moneyInApp2);
        $blocked2 = is_null($userMoney2) ? 0 : $userMoney2->getBlocked();
        $this->assertEquals($blocked, $blocked2);
    }
}