<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\CityService;
use App\Services\ModelService;
use App\Traits\DefaultController;

class ModelController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    public function __construct(ModelService $service)
    {
        $this->service = $service;
    }
}
