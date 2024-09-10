<?php

namespace App\Services;

use App\Constants\ApiEndpoints;
use App\Services\ApiService;

class TeamService
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function fetchTeams(int $leagueApiId, int $season)
    {
        $params = [
            'league' => $leagueApiId,
            'season' => $season
        ];

        $teams = $this->apiService->request(ApiEndpoints::TEAMS, $params);

        return $teams->response;
    }
}
