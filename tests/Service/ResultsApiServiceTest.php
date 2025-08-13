<?php
// tests/Service/ResultsApiServiceTest.php

namespace App\Tests\Service;

use App\Service\ResultsApiService;
use App\Service\FootballApiService;
use PHPUnit\Framework\TestCase;

class ResultsApiServiceTest extends TestCase
{
    public function testGetTeamResultsReturnsFormattedArray()
    {
        $teamId = 39;
        $season = 2023;

        $mockFixtures = [
            'response' => [
                [
                    'fixture' => [
                        'date' => '2023-08-15T19:00:00+00:00',
                        'venue' => ['name' => 'Stadium A']
                    ],
                    'teams' => [
                        'home' => ['id' => 39, 'name' => 'HomeTeam'],
                        'away' => ['id' => 55, 'name' => 'AwayTeam']
                    ],
                    'goals' => ['home' => 2, 'away' => 1]
                ],
                [
                    'fixture' => [
                        'date' => '2023-08-20T19:00:00+00:00',
                        'venue' => ['name' => 'Stadium B']
                    ],
                    'teams' => [
                        'home' => ['id' => 77, 'name' => 'OpponentHome'],
                        'away' => ['id' => 39, 'name' => 'OurTeam']
                    ],
                    'goals' => ['home' => 0, 'away' => 3]
                ]
            ]
        ];

        // Mock du FootballApiService
        $footballApiServiceMock = $this->createMock(FootballApiService::class);
        $footballApiServiceMock
            ->method('getTeamFixtures')
            ->willReturn($mockFixtures);

        $service = new ResultsApiService($footballApiServiceMock);

        $results = $service->getTeamResults($teamId, $season);

        $this->assertCount(2, $results);
        $this->assertEquals('HomeTeam', $results[0]['teamName']);
        $this->assertEquals('AwayTeam', $results[0]['opponent']);
        $this->assertEquals('2 - 1', $results[0]['score']);
        $this->assertTrue($results[0]['isHome']);

        $this->assertEquals('OurTeam', $results[1]['teamName']);
        $this->assertEquals('OpponentHome', $results[1]['opponent']);
        $this->assertEquals('0 - 3', $results[1]['score']);
        $this->assertFalse($results[1]['isHome']);
    }

    public function testGetTeamFixturesFiltersByLeague()
    {
        $teamId = 39;
        $season = 2023;
        $leagueId = 140; // Exemple Liga

        $mockFixtures = [
            'response' => [
                [
                    'league' => ['id' => 140],
                    'fixture' => ['id' => 1]
                ],
                [
                    'league' => ['id' => 39],
                    'fixture' => ['id' => 2]
                ]
            ]
        ];

        $footballApiServiceMock = $this->createMock(FootballApiService::class);
        $footballApiServiceMock
            ->method('getTeamFixtures')
            ->willReturn($mockFixtures);

        $service = new ResultsApiService($footballApiServiceMock);

        $fixtures = $service->getTeamFixtures($teamId, $season, $leagueId);

        $this->assertCount(1, $fixtures);
        $this->assertEquals(140, $fixtures[0]['league']['id']);
    }
}
