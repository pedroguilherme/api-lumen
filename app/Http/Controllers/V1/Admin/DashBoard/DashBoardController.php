<?php

namespace App\Http\Controllers\V1\Admin\DashBoard;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\EventService;
use App\Services\OfferService;
use App\Services\PublisherService;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashBoardController extends Controller
{
    private EventService $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function search(Request $request)
    {
        return $this->service->search($request->all());
    }
}
