<?php

namespace App\Http\Controllers\V1\Admin\System;

use App\Contracts\DefaultControllerContracts;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Traits\DefaultController;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
        'update' => [
            'id' => 'required|numeric',
            'vehicle_type' => 'in:C,M,T|nullable',
            'name' => 'string|nullable',
            'file' => 'string|nullable',
        ],
        'store' => [
            'vehicle_type' => 'required|in:C,M,T',
            'name' => 'required|string',
            'file' => 'string',
        ],
        'destroy' => [
            'id' => 'required|numeric',
        ]
    ];

    public function __construct(BrandService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }
}
