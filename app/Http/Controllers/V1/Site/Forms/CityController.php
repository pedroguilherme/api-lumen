<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\CityService;
use App\Traits\DefaultController;

class CityController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    public function __construct(CityService $service)
    {
        $this->service = $service;
    }
}
