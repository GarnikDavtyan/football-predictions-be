<?php

namespace App\Services;

use App\Constants\ApiEndpoints;
use App\Services\ApiService;

class RoundService
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function fetchCurrentRound(int $leagueApiId, int $season)
    {
        $params = [
            'league' => $leagueApiId,
            'season' => $season,
            'current' => 'true'
        ];

        $rounds = $this->apiService->request(ApiEndpoints::ROUNDS, $params);

        return $rounds->response[0];
    }
}
