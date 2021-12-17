<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\VersionService;
use App\Traits\DefaultController;

class VersionController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [];

    public function __construct(VersionService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }
}
