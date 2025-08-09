<?php
// src/Service/PlayerApiService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PlayerApiService
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        private string $apiKey,
        private string $publicDirectory
    ) {}

    public function getTeamNameById(string $teamId): ?string
    {
        $response = $this->client->request('GET', 'https://api-football-v1.p.rapidapi.com/v3/teams', [
            'query' => ['id' => $teamId],
            'headers' => [
                'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                'X-RapidAPI-Key' => $this->apiKey,
            ],
        ]);

        $data = $response->toArray();

        return $data['response'][0]['team']['name'] ?? null;
    }

public function getPlayersByTeam(string $teamId, int $season): array
{
    // 1. Récupérer le nom de l’équipe
    $teamName = $this->getTeamNameById($teamId) ?? 'unknown';

    // 2. Nettoyer le nom de l’équipe pour le fichier
    $slug = $this->slugify($teamName);

    // 3. Construire le nom du fichier
    $filename = "team-{$slug}-{$season}-{$teamId}-.json";
    $filepath = $this->publicDirectory . "/api/{$filename}";

    if (file_exists($filepath)) {
        $json = file_get_contents($filepath);
        $data = json_decode($json, true);
        if ($data) return $data;
    }

    $cacheKey = "players_{$teamId}_{$season}";

    $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($teamId, $season) {
        $item->expiresAfter(3600);

        $response = $this->client->request('GET', 'https://api-football-v1.p.rapidapi.com/v3/players', [
            'query' => [
                'team' => $teamId,
                'season' => $season,
            ],
            'headers' => [
                'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                'X-RapidAPI-Key' => $this->apiKey,
            ],
        ]);

        return $response->toArray();
    });

    $this->storeToPublicFolder($data, $filename);

    return $data;
}


    private function storeToPublicFolder(array $data, string $filename): void
    {
        $dir = $this->publicDirectory . '/api';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . '/' . $filename, json_encode($data));
    }private function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);

    return $text ?: 'team';
}



}
