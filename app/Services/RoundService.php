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

    public function fetchRounds(int $leagueApiId, int $season)
    {
        return $this->fetchRoundData($leagueApiId, $season);
    }

    public function fetchCurrentRound(int $leagueApiId, int $season, string $current)
    {
        return $this->fetchRoundData($leagueApiId, $season, $current)[0];
    }

    private function fetchRoundData(int $leagueApiId, int $season, ?string $current = null)
    {
        $params = [
            'league' => $leagueApiId,
            'season' => $season,
        ];

        if ($current) {
            $params['current'] = $current;
        }

        $rounds = $this->apiService->request(ApiEndpoints::ROUNDS, $params);

        return $rounds->response;
    }
}
