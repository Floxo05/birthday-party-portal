<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\RequiresPartyAccess;
use App\Entity\ChatMessage;
use App\Entity\Host;
use App\Entity\Party;
use App\Entity\User;
use App\Repository\ChatMessageRepository;
use App\Repository\PartyMemberRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/party/{id}/chat')]
final class ChatController extends AbstractController
{
    public function __construct(
        private readonly ChatMessageRepository $chatMessages,
        private readonly UserRepository $users,
        private readonly PartyMemberRepository $partyMembers,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * Room view for current user's own room.
     */
    #[Route('', name: 'chat_room_self', methods: ['GET'])]
    #[RequiresPartyAccess]
    public function roomSelf(Party $party, Request $request): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('chat/room.html.twig', [
            'party' => $party,
            'roomOwner' => $currentUser,
        ]);
    }

    /**
     * Room view for a specific owner; only accessible by the room owner or a host.
     */
    #[Route('/{ownerId}', name: 'chat_room_owner', requirements: ['ownerId' => '[0-9a-fA-F\-]{36}'], methods: ['GET'])]
    #[RequiresPartyAccess]
    public function roomForOwner(Party $party, string $ownerId): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $roomOwner = $this->users->find($ownerId);
        if (!$roomOwner instanceof User) {
            throw $this->createNotFoundException('Raum-Besitzer nicht gefunden.');
        }

        $this->denyAccessUnlessGrantedToRoom($party, $currentUser, $roomOwner);

        return $this->render('chat/room.html.twig', [
            'party' => $party,
            'roomOwner' => $roomOwner,
        ]);
    }

    /**
     * Fetch latest messages for a room as JSON.
     */
    #[Route('/{ownerId}/messages', name: 'chat_messages_list', requirements: ['ownerId' => '[0-9a-fA-F\-]{36}'], methods: ['GET'])]
    #[RequiresPartyAccess]
    public function listMessages(Party $party, string $ownerId, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $roomOwner = $this->users->find($ownerId);
        if (!$roomOwner instanceof User) {
            return new JsonResponse(['error' => 'room not found'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGrantedToRoom($party, $currentUser, $roomOwner);

        $sinceId = $request->query->get('sinceId');
        $limit = (int)($request->query->get('limit', 50));
        $limit = max(1, min($limit, 200));

        $messages = $this->chatMessages->findLatestForRoom($party, $roomOwner, is_string($sinceId) ? $sinceId : null, $limit);

        $currentUserId = (string)$currentUser->getId();
        $data = array_map(static function (ChatMessage $m) use ($currentUserId) {
            $sender = $m->getSender();
            $senderId = $sender?->getId();
            return [
                'id' => (string)$m->getId(),
                'senderId' => $senderId ? (string)$senderId : null,
                'senderName' => $sender?->getName(),
                'isOwn' => $senderId && (string)$senderId === $currentUserId,
                'content' => $m->getContent(),
                'createdAt' => $m->getCreatedAt()?->format(DATE_ATOM),
            ];
        }, $messages);

        return new JsonResponse(['items' => $data]);
    }

    /**
     * Post a new message to a room (sender is current user).
     */
    #[Route('/{ownerId}/messages', name: 'chat_messages_post', requirements: ['ownerId' => '[0-9a-fA-F\-]{36}'], methods: ['POST'])]
    #[RequiresPartyAccess]
    public function postMessage(Party $party, string $ownerId, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return new JsonResponse(['error' => 'unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $roomOwner = $this->users->find($ownerId);
        if (!$roomOwner instanceof User) {
            return new JsonResponse(['error' => 'room not found'], Response::HTTP_NOT_FOUND);
        }

        // Sender must be room owner or a host of the party
        $this->denyAccessUnlessGrantedToRoom($party, $currentUser, $roomOwner);

        $content = trim((string)($request->request->get('content') ?? $request->toArray()['content'] ?? ''));
        if ($content === '') {
            return new JsonResponse(['error' => 'empty content'], Response::HTTP_BAD_REQUEST);
        }

        $message = new ChatMessage();
        $message->setParty($party);
        $message->setRoomOwner($roomOwner);
        $message->setSender($currentUser);
        $message->setContent($content);
        $message->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($message);
        $this->em->flush();

        return new JsonResponse([
            'id' => (string)$message->getId(),
            'senderId' => (string)$currentUser->getId(),
            'senderName' => $currentUser->getName(),
            'isOwn' => true,
            'content' => $message->getContent(),
            'createdAt' => $message->getCreatedAt()->format(DATE_ATOM),
        ], Response::HTTP_CREATED);
    }

    private function denyAccessUnlessGrantedToRoom(Party $party, User $currentUser, User $roomOwner): void
    {
        $membership = $this->partyMembers->findOneByUserAndParty($currentUser, $party);
        if ($membership === null) {
            throw $this->createAccessDeniedException('Kein Zugriff auf diese Party.');
        }

        // Allowed: Owner accessing own room OR hosts accessing any room
        if ($currentUser->getId() === $roomOwner->getId()) {
            return;
        }

        if ($membership instanceof Host) {
            return;
        }

        throw $this->createAccessDeniedException('Kein Zugriff auf diesen Raum.');
    }

    /**
     * Host-only view listing all rooms for a party, sorted by latest activity.
     */
    #[Route('/rooms', name: 'chat_rooms_list', methods: ['GET'])]
    #[RequiresPartyAccess]
    public function listRooms(Party $party): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $membership = $this->partyMembers->findOneByUserAndParty($currentUser, $party);
        if (!$membership instanceof Host) {
            throw $this->createAccessDeniedException('Nur Gastgeber können alle Räume sehen.');
        }

        $rows = $this->chatMessages->findRoomsWithLastActivity($party);

        // Build a map of all party users (UUID string => User) to avoid UUID driver issues in DQL IN clauses
        $idToUser = [];
        foreach ($party->getPartyMembers() as $member) {
            $u = $member->getUser();
            if ($u instanceof User) {
                $idToUser[(string)$u->getId()] = $u;
            }
        }

        $rooms = array_map(static function (array $r) use ($idToUser) {
            $owner = $idToUser[$r['ownerId']] ?? null;
            return [
                'owner' => $owner,
                'lastAt' => $r['lastAt'],
                'messageCount' => (int)$r['messageCount'],
            ];
        }, $rows);

        return $this->render('chat/rooms.html.twig', [
            'party' => $party,
            'rooms' => array_values(array_filter($rooms, static fn($r) => $r['owner'] instanceof User)),
        ]);
    }
}


