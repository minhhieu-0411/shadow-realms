<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventory')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $player = null;

    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    #[ORM\Column]
    private int $quantity = 1;

    // ✅ NEW FIELD
    #[ORM\Column]
    private bool $equipped = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
    {
        $this->item = $item;
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = max(1, $quantity);
        return $this;
    }

    public function increaseQuantity(int $amount = 1): static
    {
        $this->quantity += $amount;
        return $this;
    }

    public function decreaseQuantity(int $amount = 1): static
    {
        $this->quantity = max(0, $this->quantity - $amount);
        return $this;
    }

    // ✅ EQUIP METHODS
    public function isEquipped(): bool
    {
        return $this->equipped;
    }

    public function setEquipped(bool $equipped): static
    {
        $this->equipped = $equipped;
        return $this;
    }
}