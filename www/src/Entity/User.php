<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class User
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private ?int $id;

    /** @ORM\Column(type="string", unique=true) */
    private string $email;

    /** @ORM\Column(type="string") */
    private string $password;

    /** @ORM\OneToMany(targetEntity="MoneyTransactionToBank", mappedBy="user") */
    private Collection $moneyTransactionToBank;

    /** @ORM\OneToMany(targetEntity="BankAccount", mappedBy="user") */
    private Collection $bankAccount;

    /** @ORM\OneToMany(targetEntity="MoneyTransactionConvert", mappedBy="user") */
    private Collection $moneyTransactionConvert;

    /**
     * @ORM\OneToOne(targetEntity="UserMoney", mappedBy="user")
     */
    private ?UserMoney $userMoney;

    /**
     * @ORM\OneToOne(targetEntity="UserPoint", mappedBy="user")
     */
    private ?UserPoint $userPoint;

    /**
     * @ORM\OneToMany(targetEntity="UserPrize", mappedBy="user")
     */
    private Collection $userPrize;

    public function __construct()
    {
        $this->moneyTransactionToBank = new ArrayCollection();
        $this->bankAccount = new ArrayCollection();
        $this->moneyTransactionConvert = new ArrayCollection();
        $this->userPrize = new ArrayCollection();
        $this->userPoint = null;
        $this->userMoney = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password, int $cost = 10)
    {
        $this->password = password_hash($password,PASSWORD_BCRYPT, $cost);
    }

    public function getMoneyTransactionToBank(): array
    {
        return $this->moneyTransactionToBank->toArray();
    }

    public function getBankAccount(): array
    {
        return $this->bankAccount->toArray();
    }

    public function getMoneyTransactionConvert(): array
    {
        return $this->moneyTransactionConvert->toArray();
    }

    public function getUserMoney(): ?UserMoney
    {
        return $this->userMoney;
    }

    public function getUserPoint(): ?UserPoint
    {
        return $this->userPoint;
    }

    public function getUserPrize(): array
    {
        return $this->userPrize->toArray();
    }
}