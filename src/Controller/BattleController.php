<?php

namespace App\Controller;

use App\Entity\Monster;
use App\Entity\Player;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BattleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {}

    private function getPlayer(Security $security): Player
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user || !$user->getPlayer()) {
            throw $this->createAccessDeniedException();
        }

        return $user->getPlayer();
    }

    #[Route('/battle', name: 'app_battle_encounter')]
    #[IsGranted('ROLE_USER')]
    public function encounter(Security $security): Response
    {
        $player = $this->getPlayer($security);
        $session = $this->requestStack->getSession();

        // Cooldown
        $lastBattle = $session->get('last_battle_time');
        if ($lastBattle && time() - $lastBattle < 2) {
            $this->addFlash('error', 'Wait a moment before next battle.');
            return $this->redirectToRoute('app_player_dashboard');
        }

        $session->set('last_battle_time', time());

        $monsters = $this->em->getRepository(Monster::class)->findAll();
        if (!$monsters) {
            $this->addFlash('error', 'No monsters available.');
            return $this->redirectToRoute('app_player_dashboard');
        }

        $monster = $monsters[array_rand($monsters)];

        // Save battle state in session
        $session->set('current_battle', [
            'monster_id' => $monster->getId(),
            'monster_health' => $monster->getHealth(),
        ]);

        return $this->render('battle/combat.html.twig', [
            'player' => $player,
            'monster' => $monster,
        ]);
    }

    #[Route('/battle/action', name: 'app_battle_action', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function action(Request $request, Security $security): JsonResponse
    {
        $player = $this->getPlayer($security);
        $session = $request->getSession();
        $battle = $session->get('current_battle');

        if (!$battle) {
            return new JsonResponse(['error' => 'No active battle'], 400);
        }

        $monster = $this->em->getRepository(Monster::class)
            ->find($battle['monster_id']);

        if (!$monster) {
            return new JsonResponse(['error' => 'Monster not found'], 400);
        }

        $monsterHealth = (int) $battle['monster_health'];
        $log = [];
        $battleOver = false;

        $action = $request->request->get('action');

        switch ($action) {
            case 'attack':

                // ===== PLAYER ATTACK =====
                $crit = rand(1, 100) <= 10;

                $damage = max(1, $player->getAttack() - $monster->getDefense() + rand(-3, 5));

                if ($crit) {
                    $damage *= 2;
                    $log[] = "🔥 Critical hit!";
                }

                $monsterHealth -= $damage;

                // ❗ FIX: prevent negative HP
                $monsterHealth = max(0, $monsterHealth);

                $log[] = "You deal {$damage} damage.";

                // ===== MONSTER DEAD =====
                if ($monsterHealth <= 0) {
                    $log[] = "🎉 Monster defeated!";

                    $player->addExperience($monster->getExpReward());
                    $player->setGold($player->getGold() + $monster->getGoldReward());

                    $battleOver = true;
                    $session->remove('current_battle');

                    break;
                }

                // ===== MONSTER ATTACK =====
                $monsterDamage = max(1, $monster->getAttack() - $player->getDefense() + rand(-2, 4));

                // ❗ FIX: prevent negative HP
                $newPlayerHp = $player->getHealth() - $monsterDamage;
                $player->setHealth(max(0, $newPlayerHp));

                $log[] = "Monster hits you for {$monsterDamage} damage.";

                // ===== PLAYER DEAD =====
                if ($player->getHealth() <= 0) {
                    $log[] = "💀 You died!";
                    $battleOver = true;
                    $session->remove('current_battle');
                }

                break;

            case 'flee':
                $log[] = "🏃 You fled from battle.";
                $battleOver = true;
                $session->remove('current_battle');
                break;
        }

        // Save updated monster HP
        if (!$battleOver) {
            $battle['monster_health'] = $monsterHealth;
            $session->set('current_battle', $battle);
        }

        $this->em->flush();

        return new JsonResponse([
            'log' => $log,
            'playerHealth' => $player->getHealth(),
            'monsterHealth' => $monsterHealth,
            'battleOver' => $battleOver,
            'gold' => $player->getGold(),
            'exp' => $player->getExperience(),
            'level' => $player->getLevel(),
        ]);
    }
}