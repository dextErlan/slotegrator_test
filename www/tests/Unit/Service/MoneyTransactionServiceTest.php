<?php

namespace Tests\Unit\Service;

use App\Entity\BankAccount;
use App\Entity\MoneyTransactionConvert;
use App\Entity\MoneyTransactionToBank;
use App\Entity\User;
use App\Exception\BankAccountNotForUser;
use App\Exception\ChangeTransactionStatusException;
use App\Service\Money\MoneyTransactionService;
use PHPUnit\Framework\TestCase;

class MoneyTransactionServiceTest extends TestCase
{
    private User $user;
    private MoneyTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->setEmail('asd@site.com');
        $this->service = new MoneyTransactionService();
    }

    public function testOpenConvertTransaction()
    {
        $transaction = $this->service->openConvertTransaction($this->user, 10);

        $this->assertInstanceOf(MoneyTransactionConvert::class, $transaction);
        $this->assertEquals($this->user, $transaction->getUser());
        $this->assertEquals(10, $transaction->getSum());
        $this->assertEquals(MoneyTransactionConvert::STATUS_OPEN, $transaction->getStatus());
    }

    public function testOpenTransferTransaction()
    {
        $bankAccount1 = new BankAccount();
        $bankAccount1->setUser($this->user)->setAccountNumber('123');
        $bankAccount2 = new BankAccount();
        $bankAccount2->setUser($this->user)->setAccountNumber('223');

        $transaction = $this->service->openTransferTransaction($this->user, 10, $bankAccount2);

        $this->assertInstanceOf(MoneyTransactionToBank::class, $transaction);
        $this->assertEquals($this->user, $transaction->getUser());
        $this->assertEquals($bankAccount2, $transaction->getBankAccount());
        $this->assertEquals(10, $transaction->getSum());
        $this->assertEquals(MoneyTransactionToBank::STATUS_OPEN, $transaction->getStatus());
    }

    public function testOpenTransferTransactionException()
    {
        $bankAccount1 = new BankAccount();
        $bankAccount1->setUser($this->user)->setAccountNumber('123');
        $user2 = new User();
        $user2->setEmail('qwe@site.edu');
        $bankAccount2 = new BankAccount();
        $bankAccount2->setUser($user2)->setAccountNumber('123');

        $this->expectException(BankAccountNotForUser::class);

        $this->service->openTransferTransaction($this->user, 10, $bankAccount2);
    }

    public function testChangeTransferTransactionStatusSuccess()
    {
        $transaction = new MoneyTransactionToBank();
        $transaction->setId(15);
        $transaction->setStatus(MoneyTransactionToBank::STATUS_OPEN);

        $this->service->changeTransferTransactionStatus($transaction, MoneyTransactionToBank::STATUS_SUCCESS);

        $this->assertEquals(MoneyTransactionToBank::STATUS_SUCCESS, $transaction->getStatus());

        $this->expectException(ChangeTransactionStatusException::class);
        $this->service->changeTransferTransactionStatus($transaction, MoneyTransactionToBank::STATUS_REFUND);
    }

    public function testChangeTransferTransactionStatusRefund()
    {
        $transaction = new MoneyTransactionToBank();
        $transaction->setId(15);
        $transaction->setStatus(MoneyTransactionToBank::STATUS_OPEN);

        $this->service->changeTransferTransactionStatus($transaction, MoneyTransactionToBank::STATUS_REFUND);

        $this->assertEquals(MoneyTransactionToBank::STATUS_REFUND, $transaction->getStatus());

        $this->expectException(ChangeTransactionStatusException::class);
        $this->service->changeTransferTransactionStatus($transaction, MoneyTransactionToBank::STATUS_SUCCESS);
    }

    public function testChangeConvertTransactionStatusSuccess()
    {
        $transaction = new MoneyTransactionConvert();
        $transaction->setId(15);
        $transaction->setStatus(MoneyTransactionConvert::STATUS_OPEN);

        $this->service->changeConvertTransactionStatus($transaction, MoneyTransactionConvert::STATUS_SUCCESS);

        $this->assertEquals(MoneyTransactionToBank::STATUS_SUCCESS, $transaction->getStatus());

        $this->expectException(ChangeTransactionStatusException::class);
        $this->service->changeConvertTransactionStatus($transaction, MoneyTransactionConvert::STATUS_REFUND);
    }

    public function testChangeConvertTransactionStatusRefund()
    {
        $transaction = new MoneyTransactionConvert();
        $transaction->setId(15);
        $transaction->setStatus(MoneyTransactionConvert::STATUS_OPEN);

        $this->service->changeConvertTransactionStatus($transaction, MoneyTransactionConvert::STATUS_REFUND);

        $this->assertEquals(MoneyTransactionToBank::STATUS_REFUND, $transaction->getStatus());

        $this->expectException(ChangeTransactionStatusException::class);
        $this->service->changeConvertTransactionStatus($transaction, MoneyTransactionConvert::STATUS_SUCCESS);
    }
}