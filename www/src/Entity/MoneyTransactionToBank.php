<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class MoneyTransactionToBank
{
    const STATUS_OPEN = 'open';
    const STATUS_SUCCESS = 'success';
    const STATUS_REFUND = 'refund';

    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private ?int $id;

    /** @ORM\Column(type="integer") */
    private int $sum;

    /** @ORM\Column(type="string") */
    private string $status;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="moneyTransactionToBank") */
    private User $user;

    /** @ORM\ManyToOne(targetEntity="BankAccount", inversedBy="moneyTransactionToBank", cascade={"persist", "remove" }) */
    private BankAccount $bankAccount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSum(): int
    {
        return $this->sum;
    }

    public function setSum(int $sum): self
    {
        $this->sum = $sum;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBankAccount(): BankAccount
    {
        return $this->bankAccount;
    }

    public function setBankAccount(BankAccount $bankAccount): self
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }
}