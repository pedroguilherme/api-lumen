<?php

namespace App\Http\Controllers\V1\Site\Structure;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Services\CityService;
use App\Services\ModelService;
use App\Services\VehicleFieldService;
use App\Services\VehicleService;
use App\Services\VersionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilterController extends Controller
{
    private BrandService $brandService;
    private ModelService $modelService;
    private VersionService $versionService;
    private VehicleFieldService $vehicleFieldService;
    private CityService $cityService;
    private VehicleService $vehicleService;

    public function __construct(
        BrandService $brandService,
        ModelService $modelService,
        VersionService $versionService,
        VehicleFieldService $vehicleFieldService,
        CityService $cityService,
        VehicleService $vehicleService
    ) {
        $this->brandService = $brandService;
        $this->modelService = $modelService;
        $this->versionService = $versionService;
        $this->vehicleFieldService = $vehicleFieldService;
        $this->cityService = $cityService;
        $this->vehicleService = $vehicleService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getBrands(Request $request)
    {
        return $this->brandService->searchSite($request->all());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getModels(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->modelService->searchSite($validator->validated());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getVersions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'model_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->versionService->searchSite($validator->validated());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getCities(Request $request)
    {
        return $this->cityService->searchSite($request->all());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getYears(Request $request)
    {
        return $this->vehicleService->searchYearsSite($request->all());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getPrices(Request $request)
    {
        return $this->vehicleService->searchPricesSite($request->all());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getFields(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->vehicleFieldService->searchSite($validator->validated());
    }
}
