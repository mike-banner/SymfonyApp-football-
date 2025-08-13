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

    /**
     * Récupère le nom de l'équipe à partir de son ID
     */
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

    /**
     * Récupère tous les joueurs d'une équipe pour une saison donnée
     * (toutes les pages de l'API sont fusionnées)
     */
    public function getPlayersByTeam(string $teamId, int $season): array
    {
        // 1. Nom de l’équipe
        $teamName = $this->getTeamNameById($teamId) ?? 'unknown';

        // 2. Nom du fichier local
        $slug = $this->slugify($teamName);
        $filename = "team-{$slug}-{$season}-{$teamId}.json";
        $filepath = $this->publicDirectory . "/api/{$filename}";

        // 3. Si fichier déjà présent -> on le lit
        if (file_exists($filepath)) {
            $json = file_get_contents($filepath);
            $data = json_decode($json, true);
            if ($data) {
                return $data;
            }
        }

        // 4. Sinon, on va chercher et on met en cache
        $cacheKey = "players_all_{$teamId}_{$season}";

        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($teamId, $season) {
            $item->expiresAfter(3600);

            $allPlayers = [];
            $page = 1;

            do {
                $response = $this->client->request('GET', 'https://api-football-v1.p.rapidapi.com/v3/players', [
                    'query' => [
                        'team'   => $teamId,
                        'season' => $season,
                        'page'   => $page
                    ],
                    'headers' => [
                        'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                        'X-RapidAPI-Key' => $this->apiKey,
                    ],
                ]);

                $result = $response->toArray();
                $players = $result['response'] ?? [];

                $allPlayers = array_merge($allPlayers, $players);

                // On récupère le nombre total de pages
                $totalPages = $result['paging']['total'] ?? 1;
                $page++;
            } while ($page <= $totalPages);

            return [
                'team' => $teamId,
                'season' => $season,
                'players' => $allPlayers
            ];
        });

        // 5. Stockage dans le dossier public/api
        $this->storeToPublicFolder($data, $filename);

        return $data;
    }

    /**
     * Sauvegarde un fichier JSON dans public/api
     */
    private function storeToPublicFolder(array $data, string $filename): void
    {
        $dir = $this->publicDirectory . '/api';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . '/' . $filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Transforme une chaîne en slug
     */
    private function slugify(string $text): string
    {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        return $text ?: 'team';
    }
}
