<?php

namespace App\Services;

use App\Constants\ApiEndpoints;
use App\Services\ApiService;

class StandingsService
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function fetchStandings(int $leagueApiId, int $season)
    {
        $params = [
            'league' => $leagueApiId,
            'season' => $season
        ];

        $standings = $this->apiService->request(ApiEndpoints::STANDINGS, $params);

        return $standings->response[0]->league->standings[0];
    }
}
