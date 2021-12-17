<?php

namespace App\Http\Controllers\V1\Admin\System;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\ModelService;
use App\Traits\DefaultController;

class ModelController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
        'update' => [
            'id' => 'required|numeric',
            'brand_id' => 'numeric|nullable',
            'name' => 'string|nullable',
        ],
        'store' => [
            'brand_id' => 'required|numeric',
            'name' => 'required|string',
        ],
        'destroy' => [
            'id' => 'required|numeric',
        ]
    ];

    public function __construct(ModelService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }
}
