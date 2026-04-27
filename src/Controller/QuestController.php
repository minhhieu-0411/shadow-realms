<?php

namespace App\Controller;

use App\Entity\Quest;
use App\Entity\Player;
use App\Enum\QuestStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QuestController extends AbstractController
{
    #[Route('/quests', name: 'app_quest_index')]
    #[IsGranted('ROLE_USER')]
    public function index(Security $security, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /** @var Player $player */
        $player = $user->getPlayer();

        if (!$player) {
            $this->addFlash('error', 'Player character not found.');
            return $this->redirectToRoute('app_register');
        }

        $availableQuests = $em->getRepository(Quest::class)->findBy(
            ['status' => QuestStatus::AVAILABLE],
            ['requiredLevel' => 'ASC']
        );

        return $this->render('quest/index.html.twig', [
            'availableQuests' => $availableQuests,
            'playerQuests'    => $player->getQuests(),
            'player'          => $player,
        ]);
    }

    #[Route('/quest/{id}', name: 'app_quest_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Quest $quest, Security $security): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /** @var Player $player */
        $player = $user->getPlayer();

        return $this->render('quest/show.html.twig', [
            'quest'  => $quest,
            'player' => $player,
        ]);
    }

    #[Route('/quest/{id}/accept', name: 'app_quest_accept', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function accept(Quest $quest, Security $security, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /** @var Player $player */
        $player = $user->getPlayer();

        if ($player->getLevel() < $quest->getRequiredLevel()) {
            $this->addFlash('error', 'You need to be level ' . $quest->getRequiredLevel() . ' to accept this quest.');
            return $this->redirectToRoute('app_quest_index');
        }

        $quest->setStatus(QuestStatus::ACCEPTED);
        $quest->setPlayer($player);
        $player->addQuest($quest);

        $em->flush();

        $this->addFlash('success', 'Quest accepted: ' . $quest->getTitle());
        return $this->redirectToRoute('app_quest_index');
    }

    #[Route('/quest/{id}/complete', name: 'app_quest_complete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function complete(Quest $quest, Security $security, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $user */
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /** @var Player $player */
        $player = $user->getPlayer();

        if ($quest->getPlayer() !== $player || $quest->getStatus() !== QuestStatus::ACCEPTED) {
            $this->addFlash('error', 'You cannot complete this quest.');
            return $this->redirectToRoute('app_quest_index');
        }

        // Give rewards
        $player->addExperience($quest->getRewardExp());
        $player->setGold($player->getGold() + $quest->getRewardGold());
        $quest->setStatus(QuestStatus::COMPLETED);

        $em->flush();

        $this->addFlash('success', 'Quest completed! You earned ' . $quest->getRewardExp() . ' XP and ' . $quest->getRewardGold() . ' Gold.');
        return $this->redirectToRoute('app_quest_index');
    }
}