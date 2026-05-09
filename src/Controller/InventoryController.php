<?php

namespace App\Controller;

use App\Entity\Inventory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class InventoryController extends AbstractController
{
   
    #[Route('/inventory', name: 'inventory_index')]
    #[IsGranted('ROLE_USER')]
    public function index(Security $security): Response
    {
        $player = $security->getUser()->getPlayer();

        return $this->render('inventory/index.html.twig', [
            'inventory' => $player->getInventory(),
            'player' => $player
        ]);
    }

    #[Route('/inventory/use/{id}', name: 'inventory_use', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function useItem(
        Inventory $inventory,
        Security $security,
        EntityManagerInterface $em
    ): Response {
        $player = $security->getUser()->getPlayer();

        if ($inventory->getPlayer() !== $player) {
            throw $this->createAccessDeniedException();
        }

        $item = $inventory->getItem();

        if ($item->getType()->value !== 'potion') {
            $this->addFlash('error', 'This item cannot be used.');
            return $this->redirectToRoute('inventory_index');
        }

        $healAmount = method_exists($item, 'getHealAmount')
            ? ($item->getHealAmount() ?? 50)
            : 50;

        $player->setHealth(
            min($player->getMaxHealth(), $player->getHealth() + $healAmount)
        );

        $inventory->decreaseQuantity();

        if ($inventory->getQuantity() <= 0) {
            $em->remove($inventory);
        }

        $em->flush();

        $this->addFlash('success', "You used {$item->getName()} and healed {$healAmount} HP!");

        return $this->redirectToRoute('inventory_index');
    }
    #[Route('/inventory/equip/{id}', name: 'inventory_equip', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function equip(
        Inventory $inventory,
        Security $security,
        EntityManagerInterface $em
    ): Response {
        $player = $security->getUser()->getPlayer();

        if ($inventory->getPlayer() !== $player) {
            throw $this->createAccessDeniedException();
        }

        $item = $inventory->getItem();

        if ($item->getType()->value === 'potion') {
            return $this->redirectToRoute('inventory_index');
        }

        foreach ($player->getInventory() as $inv) {
            if ($inv->isEquipped() && $inv->getItem()->getType() === $item->getType()) {

                $player->setAttack(
                    $player->getAttack() - ($inv->getItem()->getAttackBonus() ?? 0)
                );

                $player->setDefense(
                    $player->getDefense() - ($inv->getItem()->getDefenseBonus() ?? 0)
                );

                $inv->setEquipped(false);
            }
        }

        $inventory->setEquipped(true);

        $player->setAttack(
            $player->getAttack() + ($item->getAttackBonus() ?? 0)
        );

        $player->setDefense(
            $player->getDefense() + ($item->getDefenseBonus() ?? 0)
        );

        $em->flush();

        $this->addFlash('success', "{$item->getName()} equipped!");

        return $this->redirectToRoute('inventory_index');
    }

    #[Route('/inventory/unequip/{id}', name: 'inventory_unequip', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unequip(
        Inventory $inventory,
        Security $security,
        EntityManagerInterface $em
    ): Response {
        $player = $security->getUser()->getPlayer();

        if ($inventory->getPlayer() !== $player) {
            throw $this->createAccessDeniedException();
        }

        if (!$inventory->isEquipped()) {
            return $this->redirectToRoute('inventory_index');
        }

        $item = $inventory->getItem();

        $player->setAttack(
            $player->getAttack() - ($item->getAttackBonus() ?? 0)
        );

        $player->setDefense(
            $player->getDefense() - ($item->getDefenseBonus() ?? 0)
        );

        $inventory->setEquipped(false);

        $em->flush();

        $this->addFlash('info', "{$item->getName()} unequipped!");

        return $this->redirectToRoute('inventory_index');
    }
    #[route('/inventory/discard/{id}', name:'inventory_discard',methods:['POST'])]
    #[IsGranted('ROLE_USER')]
    public function discard(
        Inventory $inventory,
        Security $security,
        EntityManagerInterface $em
    ) :Response{
        $player = $security->getUser()->getPlayer();
        if ($inventory->getPlayer() !== $player) {
            throw $this->createAccessDeniedException();
        }
        $item =$inventory ->getItem();
        if($inventory ->isEquipped()){
             $player->getAttack() - ($item->getAttackBonus() ?? 0);
        }
        $player->setDefense(
            $player->getDefense() - ($item->getDefenseBonus() ?? 0)
        );
         $em-> remove($inventory);
         $em ->flush();
         $this ->addFlash('success',"{$item->getName()} was remove");
         return $this->redirectToRoute('inventory_index');

    }
   
}