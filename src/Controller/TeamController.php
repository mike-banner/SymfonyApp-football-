<?php

namespace App\Controller;

use App\Service\PlayerApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
   #[Route('/team/{teamId}/{season}', name: 'team_view')]
public function view(
    PlayerApiService $playerService,
    string $teamId,
    int $season
): Response {
    try {
        $playersData = $playerService->getPlayersByTeam($teamId, $season);
        $players = $playersData['response'] ?? [];

        // Appel dédié pour récupérer le nom de l'équipe
        $teamName = $playerService->getTeamNameById($teamId) ?? 'Équipe inconnue';

        return $this->render('team/view.html.twig', [
            'teamName' => $teamName,
            'season' => $season,
            'players' => $players,
        ]);
    } catch (\Throwable $e) {
        return new Response('Erreur lors du chargement des joueurs : ' . $e->getMessage(), 500);
    }
}


}
