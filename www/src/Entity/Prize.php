<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity(repositoryClass="App\Repository\PrizeRepository") */
class Prize
{
    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue */
    private ?int $id;

    /** @ORM\Column(type="string") */
    private string $name;

    /** @ORM\Column(type="integer") */
    private int $number;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }
}