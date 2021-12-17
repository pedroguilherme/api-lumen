<?php

namespace App\Http\Controllers\V1\Admin\Site;

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

    /**
     * Display a resource by Auth Publisher
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function show($id)
    {
        return $this->service->show(['id' => $id, 'all' => true]);
    }
}
