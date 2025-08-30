<?php
// src/Controller/StandingController.php

namespace App\Controller;

use App\Service\FootballApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StandingController extends AbstractController
{
    #[Route('/standing/{leagueId}/{season}', name: 'standings_view')]
    public function viewStandings(
        FootballApiService $footballApiService,
        string $leagueId,
        int $season
    ): Response {
        try {
            $currentYear = (int) date('Y');

            // Si c'est l'année en cours, le service forcera la mise à jour
            $standings = $footballApiService->getStandings($leagueId, $season);

            $leagueName = $standings['response'][0]['league']['name'] ?? 'Inconnu';

            // Générer les années disponibles pour le select
            $years = range(2020, $currentYear);
            rsort($years);

            return $this->render('standing/standing.html.twig', [
                'standings' => $standings['response'][0]['league']['standings'][0] ?? [],
                'leagueId' => $leagueId,
                'league' => $leagueName,
                'season' => $season,
                'years' => $years,
            ]);
        } catch (\Throwable $e) {
            return new Response('Erreur : ' . $e->getMessage(), 500);
        }
    }
}
