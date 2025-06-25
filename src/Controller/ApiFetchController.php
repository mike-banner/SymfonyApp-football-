<?php

namespace App\Controller;

use App\Service\FootballApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiFetchController extends AbstractController
{
    #[Route('/api/standings/{leagueId}/{season}', name: 'api_fetch_standings')]
    public function fetchStandings(
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
                'message' => 'Erreur lors de la récupération des données : ' . $e->getMessage(),
            ], 500);
        }
    }
}
