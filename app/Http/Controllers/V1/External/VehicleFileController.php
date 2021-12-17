<?php

namespace App\Http\Controllers\V1\External;

use App\Contracts\DefaultControllerContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Services\CityService;
use App\Services\VehicleFileService;
use App\Services\VehicleService;
use App\Traits\DefaultController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VehicleFileController extends Controller
{
    protected VehicleFileService $service;
    private int $publisher_id;

    private array $validator = [
        'store' => [
            'vehicle_id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric|exists:App\Models\Publisher,id',
            'file' => 'required|string',
            'order' => 'required|string',
        ],
        'order' => [
            'publisher_id' => 'required|numeric|exists:App\Models\Publisher,id',
            'vehicle_id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'order.*.id' => 'required|numeric|exists:App\Models\File,id',
            'order.*.order' => 'required|numeric',
        ],
        'destroy' => [
            'vehicle_id' => 'sometimes|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric|exists:App\Models\Publisher,id',
            'id' => 'required|numeric|exists:App\Models\File,id',
        ],
    ];

    public function __construct(VehicleFileService $service)
    {
        $this->service = $service;
        $this->publisher_id = Auth::user()->publisher_id;
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $rules = $this->validator['store'];
        $rules['vehicle_id'] .= ',publisher_id,' . $this->publisher_id;

        $validator = Validator::make(
            array_merge($request->all(), ["publisher_id" => $this->publisher_id, 'vehicle_id' => $id]),
            $rules
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        try {
            $resource = $this->service->store($validator->validated(), false);
            return Helpers::reponse(true, $this->service->makeHidenExternal($resource), 201);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rules = $this->validator['order'];

        $rules['vehicle_id'] .= ',publisher_id,' . $this->publisher_id;
        $rules['order.*.id'] .= ',vehicle_id,' . $id;

        $validator = Validator::make(array_merge($request->all(), ["publisher_id" => $this->publisher_id, 'vehicle_id' => $id]), $rules);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->update($validator->validated());
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @param int $vehicle_id
     * @param int $id
     * @return JsonResponse
     * @throws CustomException
     * @throws Exception
     */
    public function destroy(Request $request, int $vehicle_id, int $id): JsonResponse
    {
        $rules = $this->validator['destroy'];
        $rules['vehicle_id'] .= ',publisher_id,' . $this->publisher_id;
        $rules['id'] .= ',vehicle_id,' . $vehicle_id;

        $validator = Validator::make(
            [
                "id" => $id,
                'vehicle_id' => $vehicle_id,
                "publisher_id" => $this->publisher_id
            ],
            $rules
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        try {
            $resource = $this->service->destroy($validator->validated(), false);
            return Helpers::reponse(true, $this->service->makeHidenExternal($resource), 200);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }
}
