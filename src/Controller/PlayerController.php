<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PlayerController extends AbstractController
{
    #[Route('/dashboard', name: 'app_player_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $player = $user->getPlayer();

        if (!$player) {
            $this->addFlash('error', 'Player character not found. Please register again.');
            return $this->redirectToRoute('app_register');
        }

        return $this->render('player/dashboard.html.twig', [
            'player' => $player,
        ]);
    }

    #[Route('/profile', name: 'app_player_profile')]
    #[IsGranted('ROLE_USER')]
    public function profile(Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        $player = $user->getPlayer();

        return $this->render('player/profile.html.twig', [
            'player' => $player,
        ]);
    }
}