<?php

namespace App\Controller;

use App\Service\FootballApiService;
use App\Service\PlayerApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/football')]
class ApiFootballController extends AbstractController
{
    #[Route('/standings/{leagueId}/{season}', name: 'api_football_standings')]
    public function getStandings(
        FootballApiService $footballApiService,
        string $leagueId,
        int $season
    ): JsonResponse {
        try {
            $data = $footballApiService->getStandings($leagueId, $season);

            return $this->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des standings : ' . $e->getMessage(),
            ], 500);
        }
    }

    
    #[Route('/players/{teamId}/{season}', name: 'api_football_players')]
    public function getPlayers(
        PlayerApiService $playerApiService,
        string $teamId,
        int $season
    ): JsonResponse {
        try {
            $data = $playerApiService->getPlayersByTeam($teamId, $season);
            return $this->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
