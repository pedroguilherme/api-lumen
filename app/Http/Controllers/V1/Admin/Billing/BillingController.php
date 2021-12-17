<?php

namespace App\Http\Controllers\V1\Admin\Billing;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BillingService;
use App\Services\PublisherService;
use App\Traits\DefaultController;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request as RequestFacade;

class BillingController extends Controller
{
    private BillingService $service;

    /**
     * BillingController constructor.
     * @param BillingService $service
     */
    public function __construct(BillingService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function search(Request $request): JsonResponse
    {
        return $this->service->search($request->all());
    }

}
