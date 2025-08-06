<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InfoController extends AbstractController
{
    #[Route('/datenschutz', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('info/privacy.html.twig');
    }

    #[Route('/impressum', name: 'app_impressum')]
    public function legal(): Response
    {
        return $this->render('info/legal.html.twig');
    }
}
