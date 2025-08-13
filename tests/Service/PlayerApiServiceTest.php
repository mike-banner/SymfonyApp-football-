<?php

namespace App\Tests\Service;

use App\Service\PlayerApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PlayerApiServiceTest extends TestCase
{
    public function testGetPlayersByTeamAndSeasonReturnsArray()
    {
        // Mock du client HTTP
        $clientMock = $this->createMock(HttpClientInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getContent')->willReturn(json_encode([
            'team' => 39,
            'season' => 2023,
            'players' => [],
        ]));
        $clientMock->method('request')->willReturn($responseMock);

        // Mock du cache pour qu'il renvoie directement notre tableau JSON
        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock
            ->method('get')
            ->willReturn([
                'team' => 39,
                'season' => 2023,
                'players' => [],
            ]);

        $apiKey = 'dummy_api_key';
        $someParam = 'dummy_param';

        // Création du service
        $service = new PlayerApiService($clientMock, $cacheMock, $apiKey, $someParam);

        // Appel de la méthode
        $result = $service->getPlayersByTeam(39, 2023);

        // Vérifications
        $this->assertIsArray($result);
        $this->assertArrayHasKey('players', $result);
        $this->assertArrayHasKey('team', $result);
    }
}
