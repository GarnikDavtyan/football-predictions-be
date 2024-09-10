<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiService
{
    private $client;
    private $baseUrl;
    private $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.rapid_api.base_url');
        $this->headers = [
            'x-rapidapi-host' => config('services.rapid_api.host'),
            'x-rapidapi-key' => config('services.rapid_api.api_key'),
        ];
    }

    public function request(string $url, array $params = [])
    {
        try {
            $response = $this->client->request('GET', $this->baseUrl . $url, [
                'headers' => $this->headers,
                'query' =>  $params
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            throw $e;
        }
    }
}
