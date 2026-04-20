<?php

namespace App\Entity;

use App\Enum\QuestStatus;
use App\Repository\QuestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestRepository::class)]
class Quest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column]
    private int $rewardExp = 50;

    #[ORM\Column]
    private int $rewardGold = 30;

    #[ORM\Column]
    private int $requiredLevel = 1;

    #[ORM\Column(enumType: QuestStatus::class)]
    private QuestStatus $status = QuestStatus::AVAILABLE;

    #[ORM\ManyToOne(inversedBy: 'quests')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Player $player = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getRewardExp(): int
    {
        return $this->rewardExp;
    }

    public function setRewardExp(int $rewardExp): static
    {
        $this->rewardExp = $rewardExp;
        return $this;
    }

    public function getRewardGold(): int
    {
        return $this->rewardGold;
    }

    public function setRewardGold(int $rewardGold): static
    {
        $this->rewardGold = $rewardGold;
        return $this;
    }

    public function getRequiredLevel(): int
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(int $requiredLevel): static
    {
        $this->requiredLevel = $requiredLevel;
        return $this;
    }

    public function getStatus(): QuestStatus
    {
        return $this->status;
    }

    public function setStatus(QuestStatus $status): static
    {
        $this->status = $status;
        return $this;
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
}