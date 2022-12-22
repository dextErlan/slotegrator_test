<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class UserMoney
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private ?int $id;

    /** @ORM\Column(type="integer") */
    private int $moneyInApp;

    /** @ORM\Column(type="integer") */
    private int $blocked;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="userMoney")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
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

    public function getMoneyInApp(): int
    {
        return $this->moneyInApp;
    }

    public function setMoneyInApp(int $moneyInApp): self
    {
        $this->moneyInApp = $moneyInApp;

        return $this;
    }

    public function getBlocked(): int
    {
        return $this->blocked;
    }

    public function setBlocked(int $blocked): self
    {
        $this->blocked = $blocked;

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

    /**
     * Проверить доступные средства.
     *
     * @param int $sum
     * @return bool
     */
    public function isFundsAvailableForUser(int $sum): bool
    {
        return $this->moneyInApp >= $sum;
    }
}