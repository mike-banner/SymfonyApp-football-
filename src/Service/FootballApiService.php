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
        $filename = null;

        // 1. On tente de trouver le fichier local avec le NOUVEAU format
        $filePattern = $this->publicDirectory . "/api/data-*-*-{$leagueId}-{$season}.json";
        $files = glob($filePattern);
        if ($files && count($files) > 0) {
            $filename = $files[0];
            $json = file_get_contents($filename);
            $data = json_decode($json, true);
            if ($data) {
                return $data;
            }
        }

        // 2. Sinon, on va chercher l'API et on stocke le résultat en local
        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($leagueId, $season) {
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

        // 3. On récupère le nom du pays et de la ligue pour composer le nom du fichier
        if (isset($data['response'][0]['league']['country']) && isset($data['response'][0]['league']['name'])) {
            $country = $data['response'][0]['league']['country'];
            $leagueName = $data['response'][0]['league']['name'];
            $countrySlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $country), '-'));
            $leagueNameSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $leagueName), '-'));
            $filename = $this->publicDirectory . "/api/data-{$countrySlug}-{$leagueNameSlug}-{$leagueId}-{$season}.json";
        } else {
            $filename = $this->publicDirectory . "/api/data-{$leagueId}-{$season}.json";
        }
        $this->storeStandingsToPublicFolder($data, basename($filename));

        return $data;
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
