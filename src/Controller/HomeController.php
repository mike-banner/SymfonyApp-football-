<?php

namespace App\Controller;

use App\Service\LeagueProviderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(LeagueProviderService $leagueProvider): Response
    {
        $leagues = $leagueProvider->getLeagues();

        return $this->render('home/index.html.twig', [
            'leagues' => $leagues,
            'season' => 2023,
        ]);
    }
}
