<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\VehicleFieldService;
use App\Traits\DefaultController;

class VehicleFieldController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
    ];

    public function __construct(VehicleFieldService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }
}
