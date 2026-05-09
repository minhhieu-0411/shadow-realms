<?php

namespace App\Entity;

use App\Enum\CharacterClass;
use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'player', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private int $level = 1;

    #[ORM\Column]
    private int $experience = 0;

    #[ORM\Column]
    private int $health = 100;

    #[ORM\Column]
    private int $maxHealth = 100;

    #[ORM\Column]
    private int $attack = 20;

    #[ORM\Column]
    private int $defense = 10;

    #[ORM\Column]
    private int $gold = 50;

    #[ORM\Column(enumType: CharacterClass::class)]
    private CharacterClass $characterClass;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Quest::class, orphanRemoval: true)]
    private Collection $quests;

    #[ORM\OneToMany(mappedBy: 'player', targetEntity: Inventory::class, orphanRemoval: true)]
    private Collection $inventory;

    public function __construct()
    {
        $this->quests = new ArrayCollection();
        $this->inventory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
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

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;
        return $this;
    }

    public function getExperience(): int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): static
    {
        $this->experience = $experience;
        return $this;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function setHealth(int $health): static
    {
        $this->health = max(0, min($health, $this->getMaxHealth()));
        return $this;
    }

    public function getMaxHealth(): int
    {
        return $this->maxHealth;
    }

    public function setMaxHealth(int $maxHealth): static
    {
        $this->maxHealth = max(1, $maxHealth);
        return $this;
    }

    public function getAttack(): int
    {
        return $this->attack;
    }

    public function setAttack(int $attack): static
    {
        $this->attack = $attack;
        return $this;
    }

    public function getDefense(): int
    {
        return $this->defense;
    }

    public function setDefense(int $defense): static
    {
        $this->defense = $defense;
        return $this;
    }

    public function getGold(): int
    {
        return $this->gold;
    }

    public function setGold(int $gold): static
    {
        $this->gold = max(0, $gold);
        return $this;
    }

    public function getCharacterClass(): CharacterClass
    {
        return $this->characterClass;
    }

    public function setCharacterClass(CharacterClass $characterClass): static
    {
        $this->characterClass = $characterClass;
        return $this;
    }

    /**
     * @return Collection<int, Quest>
     */
    public function getQuests(): Collection
    {
        return $this->quests;
    }

    public function addQuest(Quest $quest): static
    {
        if (!$this->quests->contains($quest)) {
            $this->quests->add($quest);
            $quest->setPlayer($this);
        }
        return $this;
    }

    public function removeQuest(Quest $quest): static
    {
        if ($this->quests->removeElement($quest)) {
            if ($quest->getPlayer() === $this) {
                $quest->setPlayer(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function getInventory(): Collection
    {
        return $this->inventory;
    }

    public function addInventory(Inventory $inventory): static
    {
        if (!$this->inventory->contains($inventory)) {
            $this->inventory->add($inventory);
            $inventory->setPlayer($this);
        }
        return $this;
    }

    public function removeInventory(Inventory $inventory): static
    {
        if ($this->inventory->removeElement($inventory)) {
            if ($inventory->getPlayer() === $this) {
                $inventory->setPlayer(null);
            }
        }
        return $this;
    }


    public function addExperience(int $exp): void
    {
        $this->experience += $exp;


        $requiredExp = $this->level * 100;
        while ($this->experience >= $requiredExp) {
            $this->levelUp();
            $requiredExp = $this->level * 100;
        }
    }

    private function levelUp(): void
    {
        $this->level++;
        $this->maxHealth += 25;
        $this->health = $this->maxHealth;   // full heal on level up
        $this->attack += 6;
        $this->defense += 4;
    }

    public function isAlive(): bool
    {
        return $this->health > 0;
    }

    public function heal(int $amount): void
    {
        $this->setHealth($this->health + $amount);
    }

    public function takeDamage(int $damage): void
    {
        $this->setHealth($this->health - $damage);
    }
}