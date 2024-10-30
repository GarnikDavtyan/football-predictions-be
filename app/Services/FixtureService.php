<?php

namespace App\Services;

use App\Constants\ApiEndpoints;
use App\Models\League;
use App\Services\ApiService;

class FixtureService
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function fetchFixtures(League $league)
    {
        return $this->fetchFixturesData($league);
    }

    public function fetchFixturesByStatus(League $league, string $status)
    {
        return $this->fetchFixturesData($league, $status);
    }

    private function fetchFixturesData(League $league, ?string $status = null)
    {
        $params = [
            'league' => $league->league_api_id,
            'season' =>  $league->season,
            'round' => $league->round_api
        ];

        if ($status) {
            $params['status'] = $status;
        }

        $fixtures = $this->apiService->request(ApiEndpoints::FIXTURES, $params);

        return $fixtures->response;
    }
}
