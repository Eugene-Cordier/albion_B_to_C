<?php

namespace App\Entity;

class Item
{
    private ?int $id = null;

    private ?string $name = null;

    private ?int $sellPrice = null;

    private ?int $buyPrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSellPrice(): ?int
    {
        return $this->sellPrice;
    }

    public function setSellPrice(int $sellPrice): static
    {
        $this->sellPrice = $sellPrice;

        return $this;
    }

    public function getBuyPrice(): ?int
    {
        return $this->buyPrice;
    }

    public function setBuyPrice(int $buyPrice): static
    {
        $this->buyPrice = $buyPrice;

        return $this;
    }
}
