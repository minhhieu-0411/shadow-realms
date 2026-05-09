<?php

namespace App\Controller;

use App\Form\PlayerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Player;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PlayerController extends AbstractController
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

    #[Route('/dashboard', name: 'app_player_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(Security $security): Response
    {
        $player = $this->getPlayer($security);

        return $this->render('player/dashboard.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/profile', name: 'app_player_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Security $security): Response
    {
        $player = $this->getPlayer($security);

        return $this->render('player/profile.html.twig', [
            'player' => $player,
        ]);
    }

  #[Route('/profile/edit', name: 'app_player_edit')]
#[IsGranted('ROLE_USER')]
public function edit(
    Request $request,
    Security $security,
    EntityManagerInterface $em
): Response {
    $player = $this->getPlayer($security);

    if ($request->isMethod('POST')) {

        $name = $request->request->get('name');
        $attack = $request->request->get('attack');

        $attack = ($attack === null || $attack === '') ? 0 : (int) $attack;

        if ($name && strlen($name) >= 2) {
            $player->setName($name);
            $player->setAttack($attack);

            $em->flush();

            $this->addFlash('success', 'Profile updated!');
            return $this->redirectToRoute('app_player_profile');
        }
    }

    return $this->render('player/edit.html.twig', [
        'player' => $player,
    ]);
}
    
}