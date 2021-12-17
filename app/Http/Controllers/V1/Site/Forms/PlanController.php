<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Http\Controllers\Controller;
use App\Services\PlanService;
use App\Traits\DefaultController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    public function __construct(PlanService $service)
    {
        $this->service = $service;
    }

    public function searchPfPlans(Request $request)
    {
        return $this->service->pfPlans($request->all());
    }
}
