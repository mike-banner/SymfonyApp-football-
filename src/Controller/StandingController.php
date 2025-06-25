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
            $data = $footballApiService->getStandings($leagueId, $season);

            // Récupérer le tableau des équipes (standings)
            $standings = $data['response'][0]['league']['standings'][0] ?? [];

            $leagueName = $data['response'][0]['league']['name'] ?? 'Inconnu';

            return $this->render('standing/index.html.twig', [
                'standings' => $standings,
                'league' => $leagueName,
                'season' => $season,
            ]);
        } catch (\Throwable $e) {
            return new Response('Erreur : ' . $e->getMessage(), 500);
        }
    }
}

