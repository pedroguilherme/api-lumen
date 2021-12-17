<?php

namespace App\Http\Controllers\V1\Site\Structure;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private VehicleService $service;

    public function __construct(VehicleService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param string $vehicle
     * @return JsonResponse
     * @throws CustomException
     */
    public function show(Request $request, string $vehicle)
    {
        return $this->service->showSite(intval($vehicle));
    }
}
