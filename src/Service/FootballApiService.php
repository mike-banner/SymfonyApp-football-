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
        $this->publicDirectory = $publicDirectory;
    }

    // ta méthode pour récupérer les standings
    public function getStandings(string $leagueId, int $season): array
    {
        $cacheKey = "standings_{$leagueId}_{$season}";

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($leagueId, $season) {
            $item->expiresAfter(3600); // 1 heure

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
    }

    // nouvelle méthode pour enregistrer le JSON dans /public/api/
    public function storeStandingsToPublicFolder(array $data, string $filename): void
    {
        $dir = $this->publicDirectory . '/api';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($dir . '/' . $filename, json_encode($data));
    }
}
