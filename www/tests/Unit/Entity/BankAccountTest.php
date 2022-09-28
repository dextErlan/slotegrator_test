<?php

namespace Tests\Unit\Entity;

use App\Entity\BankAccount;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    public function testIsBelongsToUser()
    {
        $user1 = new User();
        $user1->setEmail('ni@site.com');
        $user1->setPassword('pass1');

        $bankAccount1 = new BankAccount();
        $bankAccount1->setUser($user1)->setAccountNumber('123');
        $bankAccount2 = new BankAccount();
        $bankAccount2->setUser($user1)->setAccountNumber('223');

        $user2 = new User();
        $user2->setEmail('qwe@site.edu');
        $user2->setPassword('pass2');

        $bankAccount3 = new BankAccount();
        $bankAccount3->setUser($user2)->setAccountNumber('123');

        $this->assertTrue($bankAccount1->isBelongsToUser($user1));
        $this->assertTrue($bankAccount2->isBelongsToUser($user1));
        $this->assertFalse($bankAccount3->isBelongsToUser($user1));
    }
}