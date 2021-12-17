<?php

namespace App\Http\Controllers\V1\Admin\System;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\VersionService;
use App\Traits\DefaultController;

class VersionController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
        'update' => [
            'id' => 'required|numeric',
            'model_id' => 'numeric|nullable',
            'name' => 'string|nullable',
        ],
        'store' => [
            'model_id' => 'required|numeric',
            'name' => 'required|string',
        ],
        'destroy' => [
            'id' => 'required|numeric',
        ]
    ];

    public function __construct(VersionService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }
}
