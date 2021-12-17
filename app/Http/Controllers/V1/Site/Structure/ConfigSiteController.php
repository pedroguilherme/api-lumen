<?php

namespace App\Http\Controllers\V1\Site\Structure;

use App\Http\Controllers\Controller;
use App\Services\SiteContactService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConfigSiteController extends Controller
{

    private SiteContactService $service;

    public function __construct(SiteContactService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function get(Request $request)
    {
        return $this->service->search($request->all());
    }
}
