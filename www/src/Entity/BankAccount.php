<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class BankAccount
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private ?int $id;

    /** @ORM\Column(type="string") */
    private string $accountNumber;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="bankAccount") */
    private User $user;

    /** @ORM\OneToMany(targetEntity="MoneyTransactionToBank", mappedBy="bankAccount") */
    private Collection $moneyTransactionToBank;

    public function __construct()
    {
        $this->moneyTransactionToBank = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): BankAccount
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): BankAccount
    {
        $this->user = $user;

        return $this;
    }

    public function getMoneyTransactionToBank(): array
    {
        return $this->moneyTransactionToBank->toArray();
    }
}