<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\FoodVote;
use App\Entity\PartyGroup;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Repository\FoodVoteRepository;
use App\Service\PartyGroup\PartyGroupResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/food-voting')]
#[IsGranted('ROLE_USER')]
class FoodVoteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FoodVoteRepository $foodVoteRepository,
        private readonly PartyGroupResolver $partyGroupResolver
    ) {
    }

    #[Route('/group/{id}', name: 'app_food_voting', methods: ['GET', 'POST'])]
    public function index(PartyGroup $group, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Check if group is a food voting group
        if (!$group->getIsFoodVotingGroup()) {
            throw $this->createNotFoundException('Diese Gruppe unterstützt keine Essensabstimmung.');
        }

        // Check if user is member of this group
        $partyMember = $this->getPartyMemberForUser($user, $group);
        if (!$partyMember) {
            throw $this->createAccessDeniedException('Sie sind kein Mitglied dieser Gruppe.');
        }

        // Get all food votes for this group
        $foodVotes = $this->foodVoteRepository->findBy(['group' => $group], ['createdAt' => 'DESC']);

        // Handle form submission
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            
            if ($action === 'add') {
                $this->handleAddVote($request, $group, $partyMember);
            } elseif ($action === 'edit') {
                $this->handleEditVote($request, $partyMember);
            } elseif ($action === 'delete') {
                $this->handleDeleteVote($request, $partyMember);
            }
            
            return $this->redirectToRoute('app_food_voting', ['id' => $group->getId()]);
        }

        return $this->render('food_voting/index.html.twig', [
            'group' => $group,
            'foodVotes' => $foodVotes,
            'partyMember' => $partyMember,
        ]);
    }

    private function getPartyMemberForUser(User $user, PartyGroup $group): ?PartyMember
    {
        $party = $group->getParty();
        if (!$party) {
            return null;
        }

        foreach ($party->getPartyMembers() as $partyMember) {
            if ($partyMember->getUser() === $user) {
                return $partyMember;
            }
        }

        return null;
    }

    private function handleAddVote(Request $request, PartyGroup $group, PartyMember $partyMember): void
    {
        $foodItem = trim($request->request->get('food_item', ''));
        $description = trim($request->request->get('description', ''));

        if (empty($foodItem)) {
            $this->addFlash('error', 'Bitte geben Sie ein Essen an.');
            return;
        }

        $foodVote = new FoodVote();
        $foodVote->setGroup($group);
        $foodVote->setPartyMember($partyMember);
        $foodVote->setFoodItem($foodItem);
        $foodVote->setDescription($description ?: null);

        $this->entityManager->persist($foodVote);
        $this->entityManager->flush();

        $this->addFlash('success', 'Essen erfolgreich hinzugefügt.');
    }

    private function handleEditVote(Request $request, PartyMember $partyMember): void
    {
        $voteId = $request->request->get('vote_id');
        $foodItem = trim($request->request->get('food_item', ''));
        $description = trim($request->request->get('description', ''));

        if (empty($foodItem)) {
            $this->addFlash('error', 'Bitte geben Sie ein Essen an.');
            return;
        }

        $foodVote = $this->foodVoteRepository->find($voteId);
        if (!$foodVote || $foodVote->getPartyMember() !== $partyMember) {
            $this->addFlash('error', 'Essen nicht gefunden oder Sie haben keine Berechtigung.');
            return;
        }

        $foodVote->setFoodItem($foodItem);
        $foodVote->setDescription($description ?: null);

        $this->entityManager->flush();

        $this->addFlash('success', 'Essen erfolgreich bearbeitet.');
    }

    private function handleDeleteVote(Request $request, PartyMember $partyMember): void
    {
        $voteId = $request->request->get('vote_id');

        $foodVote = $this->foodVoteRepository->find($voteId);
        if (!$foodVote || $foodVote->getPartyMember() !== $partyMember) {
            $this->addFlash('error', 'Essen nicht gefunden oder Sie haben keine Berechtigung.');
            return;
        }

        $this->entityManager->remove($foodVote);
        $this->entityManager->flush();

        $this->addFlash('success', 'Essen erfolgreich gelöscht.');
    }
}
