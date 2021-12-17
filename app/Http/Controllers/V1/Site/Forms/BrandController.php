<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Services\CityService;
use App\Traits\DefaultController;

class BrandController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }
}
