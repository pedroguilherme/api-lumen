<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\StateService;
use App\Traits\DefaultController;

class StateController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    public function __construct(StateService $service)
    {
        $this->service = $service;
    }
}
