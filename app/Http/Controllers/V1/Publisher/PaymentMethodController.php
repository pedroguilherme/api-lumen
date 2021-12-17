<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\PaymentMethodService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    protected PaymentMethodService $service;

    private array $validator = [
        'store' => [
            'payment_method' => 'required|in:CC,BOL',
            'publisher_id' => 'required|numeric',
            'holder_name' => 'required_if:payment_method,CC|string|nullable',
            'number' => 'required_if:payment_method,CC|numeric|nullable',
            'expiration_date' => 'required_if:payment_method,CC|date_format:m/Y|nullable',
            'cvv' => 'required_if:payment_method,CC|numeric|nullable',
        ],
        'update' => [
            'publisher_id' => 'required|numeric',
            'payment_method' => 'required|in:CC,BOL',
            'credit_card_id' => 'required_if:payment_method,CC|numeric|nullable',
        ],
        'destroy' => [
            'id' => 'required|numeric',
            'publisher_id' => 'required|numeric',
        ],
    ];

    private int $publisher_id;

    public function __construct(PaymentMethodService $service)
    {
        $this->service = $service;
        $this->publisher_id = Auth::user()->publisher_id;
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
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function storeByToken(Request $request)
    {
        $validator = Validator::make(
            array_merge($request->all(), ["publisher_id" => $this->publisher_id]),
            $this->validator['store']
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->store($validator->validated());
    }

    /**
     * Update resource by Auth Publisher
     *
     * @param Request $request
     * @param null $id
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateByToken(Request $request, $id = null)
    {
        $validator = Validator::make(
            array_merge($request->all(), ["publisher_id" => $this->publisher_id, "id" => $id]),
            $this->validator['update']
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->update($validator->validated());
    }

    /**
     * Destroy a resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function destroyByToken(Request $request, $id)
    {
        $validator = Validator::make(
            ['publisher_id' => $this->publisher_id, 'id' => $id],
            $this->validator['destroy']
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->destroy($validator->validated());
    }
}
