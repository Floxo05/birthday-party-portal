<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Repository\PartyMemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/party/{id}/clash', requirements: ['id' => '[0-9a-f\-]{36}'])]
class ClashController extends AbstractController
{
    public function __construct(
        private readonly PartyMemberRepository $partyMembers,
        private readonly EntityManagerInterface $em,
    ) {}

    private function getTeamNames(): array
    {
        // Under the hood stays A/B, but we display friendly names
        return [
            'A' => 'Flo',
            'B' => 'Malte',
        ];
    }

    #[Route('', name: 'party_clash_entry', methods: ['GET'])]
    public function entry(Party $party): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        $pm = $this->partyMembers->findOneByUserAndParty($user, $party);
        if (!$pm instanceof PartyMember) {
            throw $this->createAccessDeniedException();
        }

        if ($pm->getClashTeam()) {
            return $this->redirectToRoute('party_clash_start', ['id' => (string)$party->getId()]);
        }

        $counts = $this->partyMembers->getTeamCounts($party);
        return $this->render('clash/select.html.twig', [
            'party' => $party,
            'counts' => $counts,
            'teamNames' => $this->getTeamNames(),
        ]);
    }

    #[Route('/select/{side}', name: 'party_clash_select', requirements: ['side' => 'A|B'], methods: ['POST','GET'])]
    public function select(Party $party, string $side, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        $pm = $this->partyMembers->findOneByUserAndParty($user, $party);
        if (!$pm instanceof PartyMember) {
            throw $this->createAccessDeniedException();
        }

        if ($pm->getClashTeam()) {
            $this->addFlash('warning', 'Du hast bereits ein Team gewählt.');
            return $this->redirectToRoute('party_clash_start', ['id' => (string)$party->getId()]);
        }

        $counts = $this->partyMembers->getTeamCounts($party);
        $a = $counts['A'] ?? 0; $b = $counts['B'] ?? 0;
        $diff = abs($a - $b);
        if ($diff >= 5) {
            // only smaller team can be chosen
            $smaller = $a <= $b ? 'A' : 'B';
            if ($side !== $smaller) {
                $this->addFlash('danger', 'Die Team-Differenz ist zu groß. Du kannst nur das kleinere Team wählen.');
                return $this->redirectToRoute('party_clash_entry', ['id' => (string)$party->getId()]);
            }
        }

        $pm->setClashTeam($side);
        $this->em->persist($pm);
        $this->em->flush();

        return $this->redirectToRoute('party_clash_start', ['id' => (string)$party->getId()]);
    }

    #[Route('/start', name: 'party_clash_start', methods: ['GET'])]
    public function start(Party $party): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        $pm = $this->partyMembers->findOneByUserAndParty($user, $party);
        if (!$pm instanceof PartyMember) {
            throw $this->createAccessDeniedException();
        }

        if (!$pm->getClashTeam()) {
            return $this->redirectToRoute('party_clash_entry', ['id' => (string)$party->getId()]);
        }

        $counts = $this->partyMembers->getTeamCounts($party);
        $membersA = $this->partyMembers->findMembersByPartyAndTeam($party, 'A');
        $membersB = $this->partyMembers->findMembersByPartyAndTeam($party, 'B');
        return $this->render('clash/start.html.twig', [
            'party' => $party,
            'team' => $pm->getClashTeam(),
            'counts' => $counts,
            'teamMembersA' => $membersA,
            'teamMembersB' => $membersB,
            'teamNames' => $this->getTeamNames(),
        ]);
    }
}
