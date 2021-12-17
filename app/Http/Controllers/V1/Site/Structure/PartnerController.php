<?php

namespace App\Http\Controllers\V1\Site\Structure;

use App\Http\Controllers\Controller;
use App\Services\PartnerService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    private PartnerService $service;

    public function __construct(PartnerService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function get(Request $request)
    {
        return $this->service->getPartners($request->only(['name']));
    }
}
