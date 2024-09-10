<?php

namespace App\Services;

use App\Constants\ApiEndpoints;
use App\Services\ApiService;

class LeagueService
{
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function fetchLeague(string $name, string $country)
    {
        $params = [
            'name' => $name,
            'country' => $country
        ];

        $league = $this->apiService->request(ApiEndpoints::LEAGUES, $params);
        
        return $league->response[0]->league;
    }
}
