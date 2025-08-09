<?php
// src/Controller/ResultsTeamController.php

namespace App\Controller;

use App\Service\ResultsApiService;
use App\Service\TeamApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResultsTeamController extends AbstractController
{
    #[Route('/team/{id<\d+>}/results', name: 'team_results')]
    public function showResults(
        int $id,
        Request $request,
        ResultsApiService $resultsApiService,
        TeamApiService $teamApiService
    ): Response {
        $season = (int) $request->query->get('season', date('Y'));
        $league = 39; // fixe ou récupéré dynamiquement si besoin

        // Récupération des infos équipe
        $teamInfo = $teamApiService->getTeamInfo($id, $season, $league);
        $team = $teamInfo['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];

        // Récupération des résultats
        $results = $resultsApiService->getTeamResults($id, $season);

        return $this->render('team/partials/results.html.twig', [
            'team' => $team,
            'results' => $results,
            'season' => $season,
        ]);
    }
}
