<?php
// src/Service/FootballApiService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FootballApiService
{
    private HttpClientInterface $client;
    private CacheInterface $cache;
    private string $apiKey;
    private string $publicDirectory;

    public function __construct(HttpClientInterface $client, CacheInterface $cache, string $apiKey, string $publicDirectory)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->apiKey = $apiKey;
        $this->publicDirectory = rtrim($publicDirectory, '/');
    }

    public function getStandings(string $leagueId, int $season): array
    {
        $cacheKey = "standings_{$leagueId}_{$season}";
        $currentYear = (int) date('Y');
        $forceUpdate = $season === $currentYear;

        $filename = $this->getCacheFilename($leagueId, $season);

        // Charger depuis le fichier si il existe et si pas d'update forcé
        if (!$forceUpdate && file_exists($filename)) {
            $json = file_get_contents($filename);
            $data = json_decode($json, true);
            if ($data) {
                return $data;
            }
        }

        // Sinon appel à l'API via cache Symfony
        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($leagueId, $season) {
            $item->expiresAfter(3600); // 1h

            $response = $this->client->request('GET', 'https://api-football-v1.p.rapidapi.com/v3/standings', [
                'query' => [
                    'league' => $leagueId,
                    'season' => $season,
                ],
                'headers' => [
                    'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                    'X-RapidAPI-Key' => $this->apiKey,
                ],
            ]);

            return $response->toArray();
        });

        // Enregistrer le JSON
        $this->storeJson($data, $filename);

        return $data;
    }

    public function getTeamFixtures(int $teamId, int $season): array
    {
        $cacheKey = "fixtures_{$teamId}_{$season}";
        $filename = $this->publicDirectory . "/api/fixtures-team-{$teamId}-{$season}.json";
        $currentYear = (int) date('Y');
        $forceUpdate = $season === $currentYear;

        if (!$forceUpdate && file_exists($filename)) {
            $json = file_get_contents($filename);
            $data = json_decode($json, true);
            if ($data) return $data;
        }

        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($teamId, $season) {
            $item->expiresAfter(3600);

            $response = $this->client->request('GET', 'https://api-football-v1.p.rapidapi.com/v3/fixtures', [
                'query' => [
                    'team' => $teamId,
                    'season' => $season,
                    'status' => 'FT', // matchs joués
                ],
                'headers' => [
                    'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                    'X-RapidAPI-Key' => $this->apiKey,
                ],
            ]);

            return $response->toArray();
        });

        $this->storeJson($data, $filename);

        return $data;
    }

    private function getCacheFilename(string $leagueId, int $season): string
    {
        $files = glob($this->publicDirectory . "/api/data-*-*-{$leagueId}-{$season}.json");
        if ($files && count($files) > 0) {
            return $files[0];
        }

        return $this->publicDirectory . "/api/data-{$leagueId}-{$season}.json";
    }

    private function storeJson(array $data, string $filename): void
    {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
