<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));


            $playerName = $form->get('playerName')->getData();
            $characterClass = $form->get('characterClass')->getData();

            // Create and link Player
            $player = new Player();
            $player->setName($playerName);
            $player->setCharacterClass($characterClass);
            $player->setUser($user);
            $user->setPlayer($player);   

            $entityManager->persist($user);
            $entityManager->persist($player);
            $entityManager->flush();

            // Auto-login the user after registration
            return $security->login($user, 'App\\Security\\AppAuthenticator', 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}