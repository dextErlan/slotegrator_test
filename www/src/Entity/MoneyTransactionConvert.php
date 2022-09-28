<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class MoneyTransactionConvert
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

    /** @ORM\Column(type="float") */
    private float $exchangeRate;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="moneyTransactionConvert") */
    private User $user;

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

    public function getExchangeRate(): float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(string $exchangeRate): self
    {
        $this->exchangeRate = $exchangeRate;

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
}