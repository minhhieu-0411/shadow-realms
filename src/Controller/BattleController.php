<?php

namespace App\Controller;

use App\Entity\Monster;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BattleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    #[Route('/battle', name: 'app_battle_encounter')]
    #[IsGranted('ROLE_USER')]
    public function encounter(Security $security): Response
    {
        /** @var Player $player */
        $player = $security->getUser()->getPlayer();

        // Get a random monster template
        $monsterTemplates = $this->em->getRepository(Monster::class)->findAll();
        if (empty($monsterTemplates)) {
            $this->addFlash('error', 'No monsters available.');
            return $this->redirectToRoute('app_player_dashboard');
        }

        $template = $monsterTemplates[array_rand($monsterTemplates)];

        // Create a fresh copy for this battle (don't modify original)
        $monster = new Monster();
        $monster->setName($template->getName());
        $monster->setHealth($template->getHealth());
        $monster->setAttack($template->getAttack());
        $monster->setDefense($template->getDefense());
        $monster->setExpReward($template->getExpReward());
        $monster->setGoldReward($template->getGoldReward());

        // Store battle state in session
        $session = $this->container->get('request_stack')->getCurrentRequest()->getSession();
        $session->set('current_battle', [
            'player_id'      => $player->getId(),
            'monster_name'   => $monster->getName(),
            'monster_health' => $monster->getHealth(),
            'monster_attack' => $monster->getAttack(),
            'monster_defense'=> $monster->getDefense(),
            'exp_reward'     => $monster->getExpReward(),
            'gold_reward'    => $monster->getGoldReward(),
        ]);

        return $this->render('battle/combat.html.twig', [
            'player'  => $player,
            'monster' => $monster,
        ]);
    }

    #[Route('/battle/action', name: 'app_battle_action', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function action(Request $request, Security $security): JsonResponse
    {
        /** @var Player $player */
        $player = $security->getUser()->getPlayer();

        $session = $request->getSession();
        $battle = $session->get('current_battle');

        if (!$battle) {
            return new JsonResponse(['error' => 'No active battle found'], 400);
        }

        $action = $request->request->get('action');
        $log = [];
        $battleOver = false;

        $currentMonsterHealth = $battle['monster_health'];

        switch ($action) {
            case 'attack':
                // Player attacks
                $damage = max(1, $player->getAttack() - $battle['monster_defense'] + rand(-4, 6));
                $currentMonsterHealth -= $damage;
                $log[] = "You strike the {$battle['monster_name']} for <strong>{$damage}</strong> damage!";

                if ($currentMonsterHealth <= 0) {
                    $log[] = "🎉 You defeated the {$battle['monster_name']}!";
                    $player->addExperience($battle['exp_reward']);
                    $player->setGold($player->getGold() + $battle['gold_reward']);
                    $log[] = "You gained <strong>{$battle['exp_reward']} XP</strong> and <strong>{$battle['gold_reward']} Gold</strong>!";
                    $battleOver = true;
                    $session->remove('current_battle');
                } else {
                    // Monster counter-attacks
                    $monsterDamage = max(1, $battle['monster_attack'] - $player->getDefense() + rand(-3, 4));
                    $player->takeDamage($monsterDamage);
                    $log[] = "The {$battle['monster_name']} hits you for <strong>{$monsterDamage}</strong> damage!";

                    if ($player->getHealth() <= 0) {
                        $log[] = "💀 You have been defeated!";
                        $battleOver = true;
                        $session->remove('current_battle');
                    }
                }
                break;

            case 'flee':
                $log[] = "You successfully fled from battle.";
                $battleOver = true;
                $session->remove('current_battle');
                break;
        }

        // Update session with new monster health
        if (!$battleOver) {
            $battle['monster_health'] = $currentMonsterHealth;
            $session->set('current_battle', $battle);
        }

        $this->em->flush();

        return new JsonResponse([
            'log'           => $log,
            'playerHealth'  => $player->getHealth(),
            'monsterHealth' => max(0, $currentMonsterHealth),
            'battleOver'    => $battleOver,
            'gold'          => $player->getGold(),
            'experience'    => $player->getExperience(),
            'level'         => $player->getLevel(),
        ]);
    }
}