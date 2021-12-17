<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Services\VehicleFileService;
use App\Services\VehicleService;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VehicleFileController extends Controller
{
    protected DefaultServiceContracts $service;

    private array $validator = [
        'store' => [
            'file' => 'required|string',
            'order' => 'required|numeric',
            'vehicle_id' => 'numeric|nullable',
            'publisher_id' => 'required|numeric',
        ],
        'order' => [
            'vehicle_id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'order.*.id' => 'required|numeric|exists:App\Models\File,id',
            'order.*.order' => 'required|numeric',
        ],
        'destroy' => [
            'id' => 'required|numeric|exists:App\Models\File,id',
            'vehicle_id' => 'sometimes|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric',
        ],
    ];

    private int $publisher_id;

    public function __construct(VehicleFileService $service)
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
     * Destroy a resource by Auth Publisher
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroyByToken(Request $request, $id)
    {
        $rules = $this->validator['destroy'];
        $rules['vehicle_id'] .= ',publisher_id,' . $this->publisher_id;
        $rules['id'] .= ',vehicle_id,' . ($request->vehicle_id ?? 'NULL');

        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id, "publisher_id" => $this->publisher_id]),
            $rules
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->destroy($validator->validated());
    }

    /**
     * Update Order off all Resources
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ordersByToken(Request $request)
    {
        $rules = $this->validator['order'];

        $rules['vehicle_id'] .= ',publisher_id,' . $this->publisher_id;
        $rules['order.*.id'] .= ',vehicle_id,' . ($request->vehicle_id ?? null);

        $validator = Validator::make(array_merge($request->all(), ["publisher_id" => $this->publisher_id]), $rules);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->update($validator->validated());
    }
}
