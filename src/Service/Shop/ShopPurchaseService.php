<?php

declare(strict_types=1);

namespace App\Service\Shop;

use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\PurchasedItem;
use App\Entity\ShopItem;
use App\Entity\User;
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManagerInterface;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;

final class ShopPurchaseService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PartyMembershipManagerInterface $partyMembershipManager,
    ) {
    }

    public function getMaxPurchasable(PartyMember $buyer, ShopItem $item): int
    {
        $stock = max(0, $item->getQuantity());
        $price = max(0, $item->getPricePoints());
        if ($stock === 0) {
            return 0;
        }
        if ($price === 0) {
            return $stock; // free item -> limited by stock only
        }
        $byBalance = intdiv($buyer->getBalance(), $price);
        return max(0, min($stock, $byBalance));
    }

    /**
     * Performs a purchase and returns the actually purchased quantity.
     * The method clamps the requested quantity to what is possible. Returns 0 if nothing could be bought.
     */
    public function purchase(User $user, Party $party, ShopItem $item, int $requestedQty): int
    {
        $requestedQty = max(0, $requestedQty);
        if ($requestedQty === 0) {
            return 0;
        }

        // Validate associations
        if ($item->getParty()?->getId() !== $party->getId()) {
            throw new \RuntimeException('Item gehört nicht zu dieser Party.');
        }

        $buyer = $this->partyMembershipManager->getMembershipForUser($user, $party);
        if (!$buyer instanceof PartyMember) {
            throw new \RuntimeException('Kein Teilnehmer der Party.');
        }

        $max = $this->getMaxPurchasable($buyer, $item);
        if ($max === 0) {
            return 0;
        }
        $qty = min($requestedQty, $max);

        $conn = $this->entityManager->getConnection();
        $conn->beginTransaction();
        try {
            // Re-fetch with lock to avoid race conditions
            /** @var ShopItem $lockedItem */
            $lockedItem = $this->entityManager->getRepository(ShopItem::class)
                ->find($item->getId());

            $this->entityManager->lock($lockedItem, \Doctrine\ORM\LockMode::PESSIMISTIC_WRITE);

            $available = max(0, $lockedItem->getQuantity());
            $price = max(0, $lockedItem->getPricePoints());

            // recompute max with fresh data
            $buyer = $this->partyMembershipManager->getMembershipForUser($user, $party);
            $maxByBalance = $price === 0 ? $available : intdiv($buyer->getBalance(), $price);
            $maxNow = max(0, min($available, $maxByBalance));
            if ($maxNow === 0) {
                $conn->rollBack();
                return 0;
            }
            $qty = min($qty, $maxNow);

            // Apply stock change
            $lockedItem->setQuantity($available - $qty);

            // Increase spend and create PurchasedItem copies
            $totalCost = $price * $qty;
            if ($totalCost > 0) {
                $buyer->setPointsSpend($buyer->getPointsSpend() + $totalCost);
            }

            $now = new \DateTimeImmutable();
            for ($i = 0; $i < $qty; $i++) {
                $p = new PurchasedItem();
                $p->setOwner($buyer)
                    ->setName($lockedItem->getName() ?? '')
                    ->setDescription($lockedItem->getDescription())
                    ->setMedia($lockedItem->getMedia())
                    ->setAcquiredAt($now);
                $this->entityManager->persist($p);
            }

            $this->entityManager->flush();
            $conn->commit();
            return $qty;
        } catch (\Throwable $e) {
            try { $conn->rollBack(); } catch (ConnectionException) {}
            throw $e;
        }
    }
}
