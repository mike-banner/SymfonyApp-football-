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
     * Page principale d'une équipe (vue avec onglets statiques)
     */
    #[Route('/team/{teamId}/{season<\d+>}/{league<\d+>?39}', name: 'team_view', methods: ['GET'])]
    public function view(
        TeamApiService $teamService,
        string $teamId,
        int $season,
        int $league
    ): Response {
        // On récupère les infos basiques de l'équipe
        $teamFull = $teamService->getTeamInfo($teamId, $season, $league);
        $teamBasic = $teamFull['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];

        return $this->render('team/teamView.html.twig', [
            'teamId' => $teamId,
            'season' => $season,
            'league' => $league,
            'team' => $teamBasic,
            // Pas besoin de résultats/fixtures/players initialement, chargés en AJAX
            'results' => [],
            'fixtures' => [],
            'players' => []
        ]);
    }

    /**
     * Chargement AJAX des contenus des onglets
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
        $page = max(1, (int) $request->request->get('page', 1)); // Pour pagination si besoin

        if (!$teamId || $season <= 0 || !$tab) {
            return new Response('Paramètres manquants ou invalides', 400);
        }

        try {
            // Toujours récupérer les infos de base de l'équipe pour les onglets
            $teamFull = $teamService->getTeamInfo($teamId, $season, $league);
            $team = $teamFull['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];

            switch ($tab) {
                case 'players':
                    // Récupération de l'effectif via PlayerApiService (avec pagination possible)
                    $playersData = $playerService->getPlayersByTeam($teamId, $season, $page);
                    $players = $playersData['players'] ?? [];
                    $totalPages = $playersData['paging']['total'] ?? 1;

                    //dump($players); // Pour débogage
                    //exit;


                    return $this->render('team/tabs/players.html.twig', [
                        'players' => $players,
                        'team' => $team,
                        'season' => $season,
                        'currentPage' => $page,
                        'totalPages' => $totalPages,
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
