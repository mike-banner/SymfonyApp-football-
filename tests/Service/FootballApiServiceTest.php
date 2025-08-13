<?php
// tests/Service/FootballApiServiceTest.php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\FootballApiService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class FootballApiServiceTest extends TestCase
{
    public function testSomething()
    {
        $this->assertTrue(true);
    }

    public function testGetStandingsReturnsDataFromLocalFile()
    {
        $client = $this->createMock(HttpClientInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $apiKey = 'fake-key';
        $publicDir = sys_get_temp_dir();

        // Crée un faux fichier local
        $leagueId = '39';
        $season = 2020;
        $filename = $publicDir . "/api/data-test-league-{$leagueId}-{$season}.json";
        @mkdir($publicDir . '/api', 0777, true);
        file_put_contents($filename, json_encode(['foo' => 'bar']));

        $service = new FootballApiService($client, $cache, $apiKey, $publicDir);
        $result = $service->getStandings($leagueId, $season);
        $this->assertEquals(['foo' => 'bar'], $result);
        unlink($filename);
    }

    public function testGetStandingsCallsApiIfNoLocalFile()
    {
        $client = $this->createMock(HttpClientInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $apiKey = 'fake-key';
        $publicDir = sys_get_temp_dir();
        $leagueId = '39';
        $season = 2021;

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn([
            'response' => [
                [
                    'league' => [
                        'country' => 'England',
                        'name' => 'Premier League',
                    ]
                ]
            ]
        ]);

        $client->method('request')->willReturn($response);
        $cache->method('get')->willReturn([
            'response' => [
                [
                    'league' => [
                        'country' => 'England',
                        'name' => 'Premier League',
                    ]
                ]
            ]
        ]);

        $service = new FootballApiService($client, $cache, $apiKey, $publicDir);
        $result = $service->getStandings($leagueId, $season);
        $this->assertArrayHasKey('response', $result);
    }

    public function testStoreStandingsToPublicFolderCreatesFile()
    {
        $client = $this->createMock(HttpClientInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $apiKey = 'fake-key';
        $publicDir = sys_get_temp_dir();
        $service = new FootballApiService($client, $cache, $apiKey, $publicDir);
        $filename = 'test-file.json';
        $data = ['foo' => 'bar'];
        $service->storeStandingsToPublicFolder($data, $filename);
        $fullPath = $publicDir . '/api/' . $filename;
        $this->assertFileExists($fullPath);
        $this->assertEquals(json_encode($data), file_get_contents($fullPath));
        unlink($fullPath);
    }
}
