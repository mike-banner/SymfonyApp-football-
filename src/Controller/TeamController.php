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

        $teamFull = $teamService->getTeamInfo($teamId, $season, $league);

        // Séparation des données (si la réponse contient bien 'team' et 'venue')
        $teamBasic = $teamFull['team'] ?? null;

        // Fallbacks comme avant
        if (!$teamBasic && !empty($players)) {
            $teamBasic = $players[0]['team'] ?? ['name' => 'Équipe inconnue', 'logo' => null];
        }

        if (!$teamBasic) {
            $teamBasic = ['name' => 'Équipe inconnue', 'logo' => null];
        }

        return $this->render('team/view.html.twig', [
            'team' => $teamBasic,
            'season' => $season,
            'players' => $players,
        ]);
    } catch (\Throwable $e) {
        return new Response('Erreur : ' . $e->getMessage(), 500);
    }
}

}
