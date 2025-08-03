<?php

namespace App\Controller;

use App\Service\PlayerApiService;
use App\Service\TeamApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
#[Route('/team/{teamId}/{season}/{league}', name: 'team_view', defaults: ['league' => 39])]
public function view(
    PlayerApiService $playerService,
    TeamApiService $teamService,
    string $teamId,
    int $season,
    int $league // valeur par défaut en PHP aussi
    ): Response {
    try {
        $playersData = $playerService->getPlayersByTeam($teamId, $season, $league);
        $players = $playersData['response'] ?? [];

        // Récupérer les infos de l'équipe via le service TeamApiService
        $team = $teamService->getTeamInfo($teamId, $season, $league);
        dump($team);


        // Si le service ne renvoie rien, fallback sur le premier joueur (si disponible)
        if (!$team && !empty($players)) {
            $team = $players[0]['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];
        }

        if (!$team) {
            $team = ['name' => 'Équipe inconnue', 'logo' => null];
        }

        return $this->render('team/view.html.twig', [
            'team' => $team,
            'season' => $season,
            'players' => $players,
        ]);
    } catch (\Throwable $e) {
        return new Response('Erreur : ' . $e->getMessage(), 500);
    }
}

}
