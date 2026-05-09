<?php

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\Monster;
use App\Entity\Quest;
use App\Enum\CharacterClass;
use App\Enum\ItemType;
use App\Enum\QuestStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ==================== MONSTERS ====================
        $monsters = [
            ['name' => 'Goblin Scout', 'health' => 45, 'attack' => 14, 'defense' => 6, 'exp' => 25, 'gold' => 15],
            ['name' => 'Forest Wolf', 'health' => 60, 'attack' => 18, 'defense' => 8, 'exp' => 35, 'gold' => 20],
            ['name' => 'Skeleton Warrior', 'health' => 70, 'attack' => 22, 'defense' => 10, 'exp' => 45, 'gold' => 25],
             ['name' => 'Dark Imp', 'health' => 50, 'attack' => 25, 'defense' => 5, 'exp' => 40, 'gold' => 30],
            ['name' => 'Stone Golem', 'health' => 120, 'attack' => 15, 'defense' => 25, 'exp' => 80, 'gold' => 50],
        ];

        foreach ($monsters as $m) {
            $monster = new Monster();
            $monster->setName($m['name']);
            $monster->setHealth($m['health']);
            $monster->setAttack($m['attack']);
            $monster->setDefense($m['defense']);
            $monster->setExpReward($m['exp']);
            $monster->setGoldReward($m['gold']);
            $manager->persist($monster);
        }

        // ==================== ITEMS ====================
        $items = [
            ['name' => 'Iron Sword', 'type' => ItemType::WEAPON, 'value' => 50, 'atk' => 8, 'def' => 0],
            ['name' => 'Steel Axe', 'type' => ItemType::WEAPON, 'value' => 80, 'atk' => 12, 'def' => 0],
            ['name' => 'Leather Armor', 'type' => ItemType::ARMOR, 'value' => 60, 'atk' => 0, 'def' => 6],
            ['name' => 'Chainmail', 'type' => ItemType::ARMOR, 'value' => 120, 'atk' => 0, 'def' => 12],
            ['name' => 'Health Potion', 'type' => ItemType::POTION, 'value' => 30, 'atk' => 0, 'def' => 0],
            ['name' => 'Mana Potion', 'type' => ItemType::POTION, 'value' => 35, 'atk' => 0, 'def' => 0],
        ];

        foreach ($items as $i) {
            $item = new Item();
            $item->setName($i['name']);
            $item->setType($i['type']);
            $item->setValue($i['value']);
            $item->setAttackBonus($i['atk']);
            $item->setDefenseBonus($i['def']);
            $manager->persist($item);
        }

        // ==================== QUESTS ====================
        $quests = [
            ['title' => 'Clear the Goblin Camp', 'desc' => 'Eliminate 3 goblins near the forest entrance.', 'exp' => 60, 'gold' => 40, 'level' => 1],
            ['title' => 'Hunt the Alpha Wolf', 'desc' => 'The forest wolf pack is becoming dangerous. Defeat the leader.', 'exp' => 90, 'gold' => 60, 'level' => 2],
            ['title' => 'Retrieve the Lost Amulet', 'desc' => 'Find and return the ancient amulet from the ruins.', 'exp' => 120, 'gold' => 80, 'level' => 3],
        ];

        foreach ($quests as $q) {
            $quest = new Quest();
            $quest->setTitle($q['title']);
            $quest->setDescription($q['desc']);
            $quest->setRewardExp($q['exp']);
            $quest->setRewardGold($q['gold']);
            $quest->setRequiredLevel($q['level']);
            $quest->setStatus(QuestStatus::AVAILABLE);
            $manager->persist($quest);
        }

        $manager->flush();

    }
}