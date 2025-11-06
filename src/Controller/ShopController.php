<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresPartyAccess;
use App\Entity\Party;
use App\Entity\PurchasedItem;
use App\Entity\ShopItem;
use App\Entity\User;
use App\Repository\PurchasedItemRepository;
use App\Repository\ShopItemRepository;
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManagerInterface;
use App\Service\Shop\ShopPurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    public function __construct(
        private readonly ShopItemRepository $shopItemRepository,
        private readonly PartyMembershipManagerInterface $partyMembershipManager,
        private readonly ShopPurchaseService $purchaseService,
        private readonly PurchasedItemRepository $purchasedItemRepository,
    ) {
    }

    #[Route('/party/{id}/shop', name: 'shop_list')]
    #[RequiresPartyAccess]
    public function list(Party $party): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Bitte zuerst einloggen.');
        }

        $membership = $this->partyMembershipManager->getMembershipForUser($user, $party);
        $balance = $membership?->getBalance() ?? 0;

        // eager load via relation, filtered by visibility and per-user limit (general enforcement)
        $items = $party->getShopItems()->filter(fn(ShopItem $i) => $i->isVisible() && $membership && $this->purchaseService->getMaxPurchasable($membership, $i) > 0);

        // compute remaining purchasable per item for UI clamping
        $remaining = [];
        foreach ($items as $i) {
            $remaining[(string) $i->getId()] = $this->purchaseService->getMaxPurchasable($membership, $i);
        }

        return $this->render('shop/list.html.twig', [
            'party' => $party,
            'items' => $items,
            'balance' => $balance,
            'itemRemaining' => $remaining,
        ]);
    }

    #[Route('/party/{id}/purchases', name: 'purchased_list')]
    #[RequiresPartyAccess]
    public function purchases(Party $party): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Bitte zuerst einloggen.');
        }
        $membership = $this->partyMembershipManager->getMembershipForUser($user, $party);
        if ($membership === null) {
            throw $this->createAccessDeniedException('Kein Teilnehmer der Party.');
        }
        // Show all purchases of the current user (owner). Not scoped by party, by design.
        $items = $membership->getPurchasedItems();

        return $this->render('purchased/list.html.twig', [
            'party' => $party,
            'items' => $items,
        ]);
    }

    #[Route('/party/{id}/purchases/{purchasedId}', name: 'purchased_detail')]
    #[RequiresPartyAccess]
    public function purchaseDetail(Party $party, string $purchasedId): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Bitte zuerst einloggen.');
        }
        $membership = $this->partyMembershipManager->getMembershipForUser($user, $party);
        if ($membership === null) {
            throw $this->createAccessDeniedException('Kein Teilnehmer der Party.');
        }
        $purchased = $this->purchasedItemRepository->find($purchasedId);
        if (!$purchased instanceof PurchasedItem || $purchased->getOwner()?->getId() !== $membership->getId()) {
            throw $this->createNotFoundException('Gekauftes Item nicht gefunden.');
        }

        return $this->render('purchased/detail.html.twig', [
            'party' => $party,
            'item' => $purchased,
        ]);
    }

    #[Route('/party/{id}/shop/item/{itemId}', name: 'shop_item_detail')]
    #[RequiresPartyAccess]
    public function detail(Party $party, string $itemId): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Bitte zuerst einloggen.');
        }

        $item = $this->shopItemRepository->find($itemId);
        if (!$item instanceof ShopItem || $item->getParty()?->getId() !== $party->getId() || !$item->isVisible()) {
            throw $this->createNotFoundException('Item nicht gefunden.');
        }

        $membership = $this->partyMembershipManager->getMembershipForUser($user, $party);
        $balance = $membership?->getBalance() ?? 0;
        $maxPurchasable = $membership ? $this->purchaseService->getMaxPurchasable($membership, $item) : 0;

        return $this->render('shop/detail.html.twig', [
            'party' => $party,
            'item' => $item,
            'balance' => $balance,
            'maxPurchasable' => $maxPurchasable,
        ]);
    }

    #[Route('/party/{id}/shop/buy/{itemId}', name: 'shop_buy', methods: ['POST'])]
    #[RequiresPartyAccess]
    public function buy(Party $party, string $itemId, Request $request): RedirectResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Bitte zuerst einloggen.');
        }

        $item = $this->shopItemRepository->find($itemId);
        if (!$item instanceof ShopItem || $item->getParty()?->getId() !== $party->getId() || !$item->isVisible()) {
            throw $this->createNotFoundException('Item nicht gefunden.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('shop-buy-' . $item->getId(), $token)) {
            $this->addFlash('danger', 'Ungültiges Formular. Bitte versuche es erneut.');
            return $this->redirectToRoute('shop_item_detail', ['id' => $party->getId(), 'itemId' => $item->getId()]);
        }

        $qty = (int) $request->request->get('quantity', 1);
        try {
            $bought = $this->purchaseService->purchase($user, $party, $item, $qty);
            if ($bought > 0) {
                $this->addFlash('success', sprintf('Erfolgreich %d× "%s" gekauft.', $bought, $item->getName()));
            } else {
                $this->addFlash('warning', 'Kauf nicht möglich (zu wenig Punkte oder ausverkauft).');
            }
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Beim Kauf ist ein Fehler aufgetreten: ' . $e->getMessage());
        }

        // redirect back to referrer or item detail
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirectToRoute('shop_item_detail', ['id' => $party->getId(), 'itemId' => $item->getId()]);
    }
}
