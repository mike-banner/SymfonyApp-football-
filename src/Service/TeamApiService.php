<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TeamApiService
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        private string $apiKey
    ) {}

    public function getTeamInfo(string $teamId, int $season, int $league): ?array
    {
        $cacheKey = "team_info_{$teamId}_{$season}_{$league}";

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($teamId, $season, $league) {
            $item->expiresAfter(3600);

            $response = $this->client->request('GET', 'https://api-football-v1.p.rapidapi.com/v3/teams', [
                'query' => [
                    'id' => $teamId,
                    'season' => $season,
                    'league' => $league,
                ],
                'headers' => [
                    'X-RapidAPI-Host' => 'api-football-v1.p.rapidapi.com',
                    'X-RapidAPI-Key' => $this->apiKey,
                ],
            ]);

            $data = $response->toArray();
            return $data['response'][0] ?? null;
        });
    }
}
