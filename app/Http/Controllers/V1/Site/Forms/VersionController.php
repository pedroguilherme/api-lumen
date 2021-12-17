<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\CityService;
use App\Services\VersionService;
use App\Traits\DefaultController;

class VersionController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    public function __construct(VersionService $service)
    {
        $this->service = $service;
    }
}
