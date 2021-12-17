<?php

namespace App\Http\Controllers\V1\Site\Structure;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    private HomeService $service;

    public function __construct(HomeService $service)
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
        return $this->service->getSite();
    }
}
