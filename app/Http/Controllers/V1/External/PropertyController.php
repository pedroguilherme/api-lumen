<?php

namespace App\Http\Controllers\V1\External;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Services\CityService;
use App\Services\ModelService;
use App\Services\StateService;
use App\Services\VehicleFieldService;
use App\Services\VersionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PropertyController extends Controller
{
    private VehicleFieldService $vehicleFieldService;
    private BrandService $brandService;
    private ModelService $modelService;
    private VersionService $versionService;
    private CityService $cityService;
    private StateService $stateService;

    private array $validator = [
        'default' => [
            'vehicle_type' => 'required|in:C,M,T',
        ],
        'model' => [
            'brand_id' => 'required|numeric|exists:App\Models\Brand,id',
        ],
        'version' => [
            'model_id' => 'required|numeric|exists:App\Models\Model,id',
        ],
        'city' => [
            'uf' => 'required|max:2|exists:App\Models\State,uf',
        ],
    ];

    /**
     * PropertyController constructor.
     * @param VehicleFieldService $vehicleFieldService
     * @param BrandService $brandService
     * @param ModelService $modelService
     * @param VersionService $versionService
     * @param CityService $cityService
     * @param StateService $stateService
     */
    public function __construct(
        VehicleFieldService $vehicleFieldService,
        BrandService $brandService,
        ModelService $modelService,
        VersionService $versionService,
        CityService $cityService,
        StateService $stateService
    ) {
        $this->vehicleFieldService = $vehicleFieldService;
        $this->brandService = $brandService;
        $this->modelService = $modelService;
        $this->versionService = $versionService;
        $this->cityService = $cityService;
        $this->stateService = $stateService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     */
    public function fuel(Request $request): JsonResponse
    {
        return $this->searchVehicleField($request->all(), 'FUEL');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     */
    public function color(Request $request): JsonResponse
    {
        return $this->searchVehicleField($request->all(), 'COLOR');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     */
    public function accessory(Request $request): JsonResponse
    {
        return $this->searchVehicleField($request->all(), 'ACCESSORY');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     */
    public function bodyType(Request $request): JsonResponse
    {
        return $this->searchVehicleField($request->all(), 'BODY_TYPE');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     */
    public function transmission(Request $request): JsonResponse
    {
        return $this->searchVehicleField($request->all(), 'TRANSMISSION');
    }

    /**
     * @return JsonResponse
     * @throws CustomException
     * @throws Exception
     */
    public function vehicleType(): JsonResponse
    {
        return Helpers::reponse(true, Config::get('constant.vehicle_type'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws Exception
     */
    public function brand(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, ($this->validator['default'] ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->brandService->searchExternal($validator->validate());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws Exception
     */
    public function model(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, ($this->validator['model'] ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->modelService->searchExternal($validator->validate());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function version(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, ($this->validator['version'] ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->versionService->searchExternal($validator->validate());
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function state(): JsonResponse
    {
        return $this->stateService->searchExternal();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function city(Request $request): JsonResponse
    {
        $data = $request->all();

        $validator = Validator::make($data, ($this->validator['city'] ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->cityService->searchExternal($validator->validate());
    }

    /**
     * @param array $data
     * @param string $key
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     * @throws Exception
     */
    private function searchVehicleField(array $data, string $key): JsonResponse
    {
        $validator = Validator::make($data, ($this->validator['default'] ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validate();
        $data['key'] = $key;
        $data['active'] = true;
        $data['all'] = true;
        $data['orderBy'] = 'id|desc';

        return $this->vehicleFieldService->searchExternal($data);
    }

}
