<?php
namespace App\Service;

class LeagueProviderService
{
    public function getLeagues(): array
    {
        return [
            ['name' => 'Angleterre', 'id' => 39],
            ['name' => 'Allemagne',  'id' => 78],
            ['name' => 'Espagne',    'id' => 140],
            ['name' => 'France',     'id' => 61],
            ['name' => 'Italie',     'id' => 135],
        ];
    }
}