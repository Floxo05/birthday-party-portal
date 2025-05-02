<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\PartyMemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PartyMemberRepository $partyMemberRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User)
        {
            return $this->redirectToRoute('app_login');
        }

        $partyMemberships = $partyMemberRepository->findByUserOrderedByPartyDate($user);

        return $this->render('home/index.html.twig', [
            'partyMemberships' => $partyMemberships,
        ]);
    }
}
