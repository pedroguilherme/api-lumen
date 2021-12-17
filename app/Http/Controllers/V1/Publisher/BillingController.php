<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Models\Vehicle;
use App\Services\BillingService;
use App\Services\ChargesService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BillingController extends Controller
{

    protected BillingService $service;
    protected ChargesService $chargesService;
    private int $publisher_id;
    private Publisher $publisher;

    public function __construct(BillingService $service, ChargesService $chargesService)
    {
        $this->service = $service;
        $this->chargesService = $chargesService;
        $this->publisher_id = Auth::user()->publisher_id;
        $this->publisher = Auth::user()->publisher;
    }

    /**
     * Display a resource by Auth Publisher
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function searchByToken()
    {
        return $this->service->search(['publisher_id' => $this->publisher_id]);
    }

    /**
     * Display a resource by Auth Publisher
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function extractByToken()
    {
        return $this->service->extract(['publisher_id' => $this->publisher_id]);
    }

    /**
     *
     *
     * @return JsonResponse
     * @throws CustomException
     */
    public function reprocessByToken()
    {
        return $this->chargesService->reprocessPayment($this->publisher);
    }

    /**
     *
     *
     * @param int $vehicleId
     * @return JsonResponse
     * @throws CustomException
     */
    public function reprocessPFByToken(int $vehicleId)
    {
        $vehicle = Vehicle::where('id', $vehicleId)
            ->where('publisher_id', $this->publisher_id)
            ->first();

        if (!empty($vehicle)) {
            return $this->chargesService->reprocessPaymentPF($vehicle, $this->publisher);
        } else {
            return Helpers::reponse(false, [], 406, Config::get('errors.not_found'));
        }
    }
}
