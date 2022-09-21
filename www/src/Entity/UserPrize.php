<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class UserPrize
{
    const STATUS_WIN = 'win';
    const STATUS_SEND = 'send';
    const STATUS_REFUSED = 'refused';

    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private $id;

    /** @ORM\Column(type="string") */
    private string $status;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="userPrize") */
    private User $user;

    /** @ORM\ManyToOne(targetEntity="Prize") */
    private Prize $prize;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrize(): Prize
    {
        return $this->prize;
    }

    public function setPrize(Prize $prize): self
    {
        $this->prize = $prize;

        return $this;
    }
}