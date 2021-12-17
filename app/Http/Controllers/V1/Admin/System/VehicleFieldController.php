<?php

namespace App\Http\Controllers\V1\Admin\System;

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
        'update' => [
            'id' => 'required|numeric',
            'vehicle_type' => 'in:C,M,T|nullable',
            'key' => 'string|nullable',
            'value' => 'string|nullable',
        ],
        'store' => [
            'vehicle_type' => 'in:C,M,T|nullable',
            'key' => 'required|string|nullable',
            'value' => 'required|string|nullable',
        ],
        'destroy' => [
            'id' => 'required|numeric',
        ]
    ];

    public function __construct(VehicleFieldService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }
}
