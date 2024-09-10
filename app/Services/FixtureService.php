<?php

namespace App\Services;

use App\Constants\ApiEndpoints;
use App\Services\ApiService;

class FixtureService
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function fetchFixtures(int $leagueApiId, int $season, int $round)
    {
        return $this->fetchFixturesData($leagueApiId, $season, $round);
    }

    public function fetchFixturesByStatus(int $leagueApiId, int $season, string $round, string $status)
    {
        return $this->fetchFixturesData($leagueApiId, $season, $round, $status);
    }

    private function fetchFixturesData(int $leagueApiId, int $season, int $round, ?string $status = null)
    {
        $params = [
            'league' => $leagueApiId,
            'season' => $season,
            'round' => 'Regular Season - ' . $round
        ];

        if ($status) {
            $params['status'] = $status;
        }

        $fixtures = $this->apiService->request(ApiEndpoints::FIXTURES, $params);

        return $fixtures->response;
    }
}
