<?php

namespace App\Entity;

use App\Enum\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(enumType: ItemType::class)]
    private ItemType $type;

    #[ORM\Column]
    private int $value = 10;   // gold price or sell value

    #[ORM\Column(nullable: true)]
    private ?int $attackBonus = null;

    #[ORM\Column(nullable: true)]
    private ?int $defenseBonus = null;

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

    public function getType(): ItemType
    {
        return $this->type;
    }

    public function setType(ItemType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getAttackBonus(): ?int
    {
        return $this->attackBonus;
    }

    public function setAttackBonus(?int $attackBonus): static
    {
        $this->attackBonus = $attackBonus;
        return $this;
    }

    public function getDefenseBonus(): ?int
    {
        return $this->defenseBonus;
    }

    public function setDefenseBonus(?int $defenseBonus): static
    {
        $this->defenseBonus = $defenseBonus;
        return $this;
    }
}