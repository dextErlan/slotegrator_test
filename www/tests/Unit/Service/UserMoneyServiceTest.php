<?php

namespace Tests\Unit\Service;

use App\Entity\User;
use App\Entity\UserMoney;
use App\Exception\BlockedSumNotValid;
use App\Exception\FundsNotAvailableForUser;
use App\Service\Money\UserMoneyService;
use PHPUnit\Framework\TestCase;

class UserMoneyServiceTest extends TestCase
{
    private User $user;
    private UserMoneyService $moneyService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->setEmail('asd@site.com');
        $this->moneyService = new UserMoneyService($this->user);
    }

    public function testGetNewUserMoney(): void
    {
        $userMoney = $this->moneyService->getUserMoney();

        $this->assertInstanceOf(UserMoney::class, $userMoney);
        $this->assertEquals(0, $userMoney->getMoneyInApp());
        $this->assertEquals(0, $userMoney->getBlocked());
    }

    public function testAddMoney(): void
    {
        $userMoney = $this->moneyService->getUserMoney();
        $userMoney->setMoneyInApp(100);
        $userMoney->setBlocked(100);

        $this->moneyService->addMoney(10);

        $this->assertEquals(110, $userMoney->getMoneyInApp());
        $this->assertEquals(100, $userMoney->getBlocked());
    }

    public function testWithdrawalBlockMoney(): void
    {
        $userMoney = $this->moneyService->getUserMoney();
        $userMoney->setMoneyInApp(90);
        $userMoney->setBlocked(50);

        $this->moneyService->withdrawalBlockMoney(10);

        $this->assertEquals(40, $userMoney->getBlocked());
    }

    public function testWithdrawalBlockMoneyException(): void
    {
        $this->expectException(BlockedSumNotValid::class);

        $this->moneyService->withdrawalBlockMoney(10);
    }

    public function testBlockMoney(): void
    {
        $userMoney = $this->moneyService->getUserMoney();
        $userMoney->setMoneyInApp(70);
        $userMoney->setBlocked(25);

        $this->moneyService->blockMoney(10);

        $this->assertEquals(60, $userMoney->getMoneyInApp());
        $this->assertEquals(35, $userMoney->getBlocked());
    }

    public function testBlockMoneyException(): void
    {
        $this->expectException(FundsNotAvailableForUser::class);

        $this->moneyService->blockMoney(10);
    }

    public function testRefundMoney(): void
    {
        $userMoney = $this->moneyService->getUserMoney();
        $userMoney->setMoneyInApp(100);
        $userMoney->setBlocked(100);

        $this->moneyService->refundMoney(10);

        $this->assertEquals(110, $userMoney->getMoneyInApp());
        $this->assertEquals(90, $userMoney->getBlocked());
    }

    public function testRefundMoneyException(): void
    {
        $this->expectException(BlockedSumNotValid::class);

        $this->moneyService->refundMoney(10);
    }
}