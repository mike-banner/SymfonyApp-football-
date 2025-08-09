<?php
// src/Controller/TeamController.php

namespace App\Controller;

use App\Service\PlayerApiService;
use App\Service\TeamApiService;
use App\Service\ResultsApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    /**
     * Page principale d'une équipe — affiche la structure avec onglets (contenu chargé ensuite via AJAX).
     */
    #[Route('/team/{teamId}/{season<\d+>}/{league<\d+>?39}', name: 'team_view', methods: ['GET'])]
    public function view(
        TeamApiService $teamService,
        string $teamId,
        int $season,
        int $league
    ): Response {
        dump($teamId, $season, $league);
        // Récupération des informations de base pour l'équipe (nom, logo, ...)
        $teamFull = $teamService->getTeamInfo($teamId, $season, $league);
        $teamBasic = $teamFull['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];



        dump($teamFull);

        return $this->render('team/teamView.html.twig', [
            'teamId' => $teamId,
            'season' => $season,
            'league' => $league,
            'team' => $teamBasic,
            'results' => $results ?? [],
        ]);
    }

    /**
     * Endpoint AJAX pour charger dynamiquement le contenu d'un onglet.
     * Reçoit POST { teamId, season, league, tab } et renvoie le HTML partiel correspondant.
     */
   #[Route('/team/load-tab', name: 'team_load_tab', methods: ['POST'])]
public function loadTab(
    Request $request,
    PlayerApiService $playerService,
    ResultsApiService $resultsApiService,
    TeamApiService $teamService
): Response {
    $teamId = (string) $request->request->get('teamId', '');
    $season = (int) $request->request->get('season', 0);
    $league = (int) $request->request->get('league', 0);
    $tab = (string) $request->request->get('tab', '');

    if (empty($teamId) || $season <= 0 || empty($tab)) {
        return new Response('Paramètres manquants ou invalides', 400);
    }

    try {
        $teamFull = $teamService->getTeamInfo($teamId, $season, $league);
        $team = $teamFull['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];

        switch ($tab) {
            case 'players':
                $playersData = $playerService->getPlayersByTeam($teamId, $season);
                $players = $playersData['response'] ?? [];

                return $this->render('team/tabs/players.html.twig', [
                    'players' => $players,
                    'team' => $team,
                    'season' => $season,
                ]);

            case 'results':
                $results = $resultsApiService->getTeamResults($teamId, $season);

                return $this->render('team/tabs/results.html.twig', [
                    'matches' => $results,
                    'team' => $team,
                    'season' => $season,
                ]);

            case 'fixtures':
                $fixtures = $resultsApiService->getTeamFixtures($teamId, $season, $league);

                return $this->render('team/tabs/fixtures.html.twig', [
                    'fixtures' => $fixtures,
                    'team' => $team,
                    'season' => $season,
                ]);

            case 'infos':
                return $this->render('team/tabs/infos.html.twig', [
                    'teamId' => $teamId,
                    'season' => $season,
                    'league' => $league,
                    'team' => $team,
                ]);

            case 'stats':
                return $this->render('team/tabs/stats.html.twig', [
                    'teamId' => $teamId,
                    'season' => $season,
                    'league' => $league,
                    'team' => $team,
                ]);

            default:
                return new Response('Onglet inconnu', 400);
        }
    } catch (\Throwable $e) {
        return new Response('Erreur serveur : ' . $e->getMessage(), 500);
    }
}


}
