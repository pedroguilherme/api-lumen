<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\OfferService;
use App\Services\PublisherService;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    private array $validator = [];

    private int $publisher_id;

    private OfferService $service;

    public function __construct(OfferService $service)
    {
        $this->service = $service;
        $this->publisher_id = Auth::user()->publisher_id;
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function searchByToken(Request $request)
    {
        return $this->service->search(array_merge($request->all(),
            ["publisher_id" => $this->publisher_id, 'origin' => 'token']));
    }

    /**
     * Display a resource by Auth Publisher
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function showByToken($id)
    {
        return $this->service->show(['id' => $id, 'publisher_id' => $this->publisher_id, 'origin' => 'token']);
    }
}
