<?php

namespace Tests\Feature\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Exception\BankAccountNotForUser;
use App\Exception\BankAPIException;
use App\Exception\FundsNotAvailableForUser;
use App\Exception\TransferException;
use App\Service\MoneyService;
use App\Service\MoneyTransactionService;
use App\Service\PointServiceInterface;
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
        $this->user = $em->getRepository(User::class)->findOneBy(['email' => 'first@user.test']);
        $this->moneyService = new MoneyService($this->user, $em);
        $this->moneyService->addMoney(10);
    }

    public function testGiveawayZeroWhenLimitEnd()
    {
        $limitForGifts = [0, -1];

        foreach ($limitForGifts as $limit) {
            list($giftCount, $giftDescription) = $this->moneyService->giveaway($limit);

            $this->assertEquals(0, $giftCount);
            $this->assertEquals("0 долларов", $giftDescription);
        }
    }

    public function testGiveawayMoney()
    {
        $limitForGifts = 54;
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();

        list($giftCount, $giftDescription) = $this->moneyService->giveaway($limitForGifts);

        $this->assertGreaterThan(0, $giftCount);
        $this->assertLessThanOrEqual($limitForGifts, $giftCount);
        $this->assertEquals($moneyInApp + $giftCount, $this->user->getUserMoney()->getMoneyInApp());
    }

    public function testOpenTransferWithWrongBankAccount()
    {
        $sum = 10;
        $bankAccount = new BankAccount();
        $bankAccount->setAccountNumber('444');

        $this->expectException(BankAccountNotForUser::class);

        $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);
    }

    public function testOpenTransferWhenMoneyNotEnough()
    {
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $sum = $moneyInApp + 10;
        $bankAccount = ($this->user->getBankAccount())[0];

        $this->expectException(FundsNotAvailableForUser::class);

        $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);
    }

    public function testOpenTransferSuccessAndRefund()
    {
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $sum = $moneyInApp;
        $bankAccount = ($this->user->getBankAccount())[0];

        $transaction = $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);

        // Проверяем что деньги заблокировались и создалась запись в транзакциях
        $this->assertEquals(0, $this->user->getUserMoney()->getMoneyInApp());
        $this->assertEquals($sum, $this->user->getUserMoney()->getBlocked());
        $this->assertInstanceOf(MoneyTransactionToBank::class, $transaction);
        $this->assertEquals($sum, $transaction->getSum());
        $this->assertEquals(MoneyTransactionService::STATUS_OPEN, $transaction->getStatus());

        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $blocked = $this->user->getUserMoney()->getBlocked();

        $bankAPIService = $this->createMock(RequestToBankAPIServiceInterface::class);
        $bankAPIService->method('fetchTransferMoneyToBank')
            ->willThrowException(new BankAPIException);

        try{
            $this->moneyService->transferMoney($transaction, $bankAPIService);
        } catch(Exception $e){
            // Проверяем что деньги вернулись
            $this->assertEquals($sum, $this->user->getUserMoney()->getMoneyInApp());
            $this->assertEquals(0, $this->user->getUserMoney()->getBlocked());
            $this->assertEquals(MoneyTransactionService::STATUS_REFUND, $transaction->getStatus());
            $this->assertInstanceOf(TransferException::class, $e);
        }
    }

    public function testTransferSuccess()
    {
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $sum = $moneyInApp;
        $bankAccount = ($this->user->getBankAccount())[0];

        $transaction = $this->moneyService->setTaskForTransferMoney($sum, $bankAccount);

        // Проверяем что деньги заблокировались и создалась запись в транзакциях
        $this->assertEquals(0, $this->user->getUserMoney()->getMoneyInApp());
        $this->assertEquals($sum, $this->user->getUserMoney()->getBlocked());
        $this->assertInstanceOf(MoneyTransactionToBank::class, $transaction);
        $this->assertEquals($sum, $transaction->getSum());
        $this->assertEquals(MoneyTransactionService::STATUS_OPEN, $transaction->getStatus());

        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $blocked = $this->user->getUserMoney()->getBlocked();

        $bankAPIService = $this->createMock(RequestToBankAPIServiceInterface::class);
        $bankAPIService->method('fetchTransferMoneyToBank')
            ->willReturn([200, ['msg' => 'success']]);

        $this->moneyService->transferMoney($transaction, $bankAPIService);

        // Проверяем что списались
        $this->assertEquals(0, $this->user->getUserMoney()->getMoneyInApp());
        $this->assertEquals(0, $this->user->getUserMoney()->getBlocked());
        $this->assertEquals(MoneyTransactionService::STATUS_SUCCESS, $transaction->getStatus());
    }

    public function testTransferWithWrongBankAccount()
    {
        // TODO
        $this->assertTrue(true);
    }

    public function testTransferWhenMoneyNotEnough()
    {
        // TODO
        $this->assertTrue(true);
    }

    public function testOpenConvertSuccessAndRefund()
    {
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $sum = $moneyInApp;
        $blocked = $this->user->getUserMoney()->getBlocked();

        $pointService = $this->createMock(PointServiceInterface::class);
        $pointService->method('addPoint')
            ->willThrowException(new Exception);

        try {
            $this->moneyService->convertToUserPoints($sum, $pointService);
        } catch(Exception $e){
            // Проверяем что деньги остались в прежнем состоянии
            $this->assertEquals($moneyInApp, $this->user->getUserMoney()->getMoneyInApp());
            $this->assertEquals($blocked, $this->user->getUserMoney()->getBlocked());
            $this->assertInstanceOf(TransferException::class, $e);
        }
    }

    public function testConvertSuccess()
    {
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();
        $sum = $moneyInApp;

        $pointService = $this->createMock(PointServiceInterface::class);

        $this->moneyService->convertToUserPoints($sum, $pointService);

        // Проверяем что списались
        $this->assertEquals(0, $this->user->getUserMoney()->getMoneyInApp());
        $this->assertEquals(0, $this->user->getUserMoney()->getBlocked());
    }

    public function testIsFundsAvailableForUser()
    {
        $moneyInApp = $this->user->getUserMoney()->getMoneyInApp();

        $this->assertFalse($this->moneyService->isFundsAvailableForUser($moneyInApp+1));
        $this->assertTrue($this->moneyService->isFundsAvailableForUser($moneyInApp));
        $this->assertTrue($this->moneyService->isFundsAvailableForUser($moneyInApp-1));
    }
}