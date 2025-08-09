<?php
// src/Service/ResultsApiService.php


namespace App\Service;

class ResultsApiService
{
    private FootballApiService $footballApiService;

    public function __construct(FootballApiService $footballApiService)
    {
        $this->footballApiService = $footballApiService;
    }

    public function getTeamResults(int $teamId, int $season): array
    {
        $data = $this->footballApiService->getTeamFixtures($teamId, $season);

        $fixtures = $data['response'] ?? [];
        $results = [];

        foreach ($fixtures as $fixture) {
            $match = $fixture['fixture'];
            $teams = $fixture['teams'];
            $goals = $fixture['goals'];

            $isHome = $teams['home']['id'] === $teamId;

        $results[] = [
            'date' => $match['date'],
            'teamName' => $isHome ? $teams['home']['name'] : $teams['away']['name'],
            'opponent' => $isHome ? $teams['away']['name'] : $teams['home']['name'],
            'isHome' => $isHome,
            'score' => $goals['home'] . ' - ' . $goals['away'],
            'venue' => $match['venue']['name'] ?? 'N/A',
        ];
        }

        return $results;
    }
    public function getTeamFixtures(int $teamId, int $season, int $league): array
    {
        // Ici on réutilise FootballApiService, soit tu ajoutes une méthode dédiée dans FootballApiService
        // Soit tu filtres directement les fixtures déjà récupérées.
        
        $allFixtures = $this->footballApiService->getTeamFixtures($teamId, $season);
        
        // Puis tu filtres si besoin sur la ligue $league, ou tu modifies FootballApiService pour l'ajouter
        
        // Exemple simple de filtre :
        $fixtures = array_filter($allFixtures['response'] ?? [], function ($fixture) use ($league) {
            return isset($fixture['league']['id']) && $fixture['league']['id'] == $league;
        });
        
        return $fixtures;
    }


}
