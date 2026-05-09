<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Player;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ShopController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    private function getPlayer(Security $security): Player
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->em->getRepository(Player::class)
            ->findOneBy(['user' => $user]);
    }

    #[Route('/shop', name: 'shop_index')]
    public function index(Security $security): Response
    {
        $player = $this->getPlayer($security);

        return $this->render('shop/index.html.twig', [
            'items' => $this->em->getRepository(Item::class)->findAll(),
            'player' => $player
        ]);
    }

    #[Route('/shop/buy/{id}', name: 'shop_buy', methods: ['POST'])]
    public function buy(Item $item, Security $security): Response
    {
        $player = $this->getPlayer($security);

        if ($player->getGold() < $item->getValue()) {
            return $this->redirectToRoute('shop_index');
        }

        $player->setGold($player->getGold() - $item->getValue());

        $inv = $this->em->getRepository(Inventory::class)
            ->findOneBy(['player' => $player, 'item' => $item]);

        if ($inv) {
            $inv->increaseQuantity();
        } else {
            $inv = new Inventory();
            $inv->setPlayer($player);
            $inv->setItem($item);
            $inv->setQuantity(1);

            $this->em->persist($inv);
        }

        $this->em->flush();

        return $this->redirectToRoute('shop_index');
    }
}
//w