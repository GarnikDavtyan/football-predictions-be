<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiService;

abstract class BaseCommand extends Command
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }
}