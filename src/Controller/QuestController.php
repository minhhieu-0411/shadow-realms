<?php

namespace App\Controller;

use App\Entity\Quest;
use App\Entity\Player;
use App\Entity\User;
use App\Enum\QuestStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class QuestController extends AbstractController
{
    private function getPlayer(Security $security): Player
    {
        /** @var User $user */
        $user = $security->getUser();

        if (!$user || !$user->getPlayer()) {
            throw $this->createAccessDeniedException('Player not found');
        }

        return $user->getPlayer();
    }

    #[Route('/quests', name: 'app_quest_index')]
    #[IsGranted('ROLE_USER')]
    public function index(Security $security, EntityManagerInterface $em): Response
    {
        $player = $this->getPlayer($security);

        $availableQuests = $em->getRepository(Quest::class)->findBy([
            'status' => QuestStatus::AVAILABLE
        ]);

        return $this->render('quest/index.html.twig', [
            'availableQuests' => $availableQuests,
            'playerQuests' => $player->getQuests(),
            'player' => $player,
        ]);
    }

    #[Route('/quest/{id}', name: 'app_quest_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Quest $quest, Security $security): Response
    {
        $player = $this->getPlayer($security);

        return $this->render('quest/show.html.twig', [
            'quest' => $quest,
            'player' => $player,
        ]);
    }

    #[Route('/quest/{id}/accept', name: 'app_quest_accept', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function accept(Quest $quest, Security $security, EntityManagerInterface $em): Response
    {
        $player = $this->getPlayer($security);

        if ($player->getLevel() < $quest->getRequiredLevel()) {
            $this->addFlash('error', 'Level too low.');
            return $this->redirectToRoute('app_quest_index');
        }

        if ($quest->getStatus() !== QuestStatus::AVAILABLE) {
            $this->addFlash('error', 'Quest not available.');
            return $this->redirectToRoute('app_quest_index');
        }

        $quest->setPlayer($player);
        $quest->setStatus(QuestStatus::ACCEPTED);

        $em->flush();

        $this->addFlash('success', 'Quest accepted!');

        return $this->redirectToRoute('app_quest_index');
    }

    #[Route('/quest/{id}/complete', name: 'app_quest_complete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function complete(Quest $quest, Security $security, EntityManagerInterface $em): Response
    {
        $player = $this->getPlayer($security);

        if (
            $quest->getPlayer() !== $player ||
            $quest->getStatus() !== QuestStatus::ACCEPTED
        ) {
            $this->addFlash('error', 'You cannot complete this quest.');
            return $this->redirectToRoute('app_quest_index');
        }

        // Rewards
        $player->addExperience($quest->getRewardExp());
        $player->setGold($player->getGold() + $quest->getRewardGold());

        $quest->setStatus(QuestStatus::COMPLETED);

        $em->flush();

        $this->addFlash('success', 'Quest completed!');

        return $this->redirectToRoute('app_quest_index');
    }
}