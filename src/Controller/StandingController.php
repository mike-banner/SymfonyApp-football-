<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StandingController extends AbstractController
{
    #[Route('/', name: 'app_standing')]
    public function index(): Response
    {
        return $this->render('standing/index.html.twig', [
            'controller_name' => 'StandingController',
        ]);
    }
}
