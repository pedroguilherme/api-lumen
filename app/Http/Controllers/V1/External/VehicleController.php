<?php

namespace App\Http\Controllers\V1\External;

use App\Contracts\DefaultControllerContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BrandService;
use App\Services\CityService;
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

class VehicleController extends Controller
{
    protected VehicleService $service;
    private int $publisher_id;

    private array $validator = [
        'store' => [
            'publisher_id' => 'required|numeric',
            'type' => 'required|in:C,M,T',
            'plate' => 'required|regex:/[A-Za-z]{3}[0-9][0-9A-Za-z][0-9]{2}/',
            'year_manufacture' => 'required|numeric|digits:4',
            'year_model' => 'required|numeric|digits:4',
            'mileage' => 'required|numeric',
            'doors' => 'required|numeric|nullable',
            'version_id' => 'required|numeric|exists:App\Models\Version,id',
            'fuel_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,FUEL',
            'transmission_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,TRANSMISSION',
            'color_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,COLOR',
            'bodytype_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,BODY_TYPE',
            'accessories.*' => 'nullable|exists:App\Models\VehicleField,id,key,ACCESSORY',
            'value' => 'required|numeric',
            'city_id' => 'required|numeric|exists:App\Models\City,id',
            'description' => 'required|max:500',
            'delivery' => 'required|boolean',
            'warranty' => 'required|boolean',
            'armored' => 'required|boolean',
            'only_owner' => 'required|boolean',
            'seven_places' => 'required|boolean',
            'review' => 'required|boolean',
            'spotlight' => 'required|in:N,S,G,D',
        ],
        'update' => [
            'id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric|exists:App\Models\Publisher,id',
            'type' => 'required|in:C,M,T',
            'plate' => 'required|regex:/[A-Za-z]{3}[0-9][0-9A-Za-z][0-9]{2}/',
            'year_manufacture' => 'required|numeric|digits:4',
            'year_model' => 'required|numeric|digits:4',
            'mileage' => 'required|numeric',
            'doors' => 'required|numeric|nullable',
            'version_id' => 'required|numeric|exists:App\Models\Version,id',
            'fuel_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,FUEL',
            'transmission_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,TRANSMISSION',
            'color_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,COLOR',
            'bodytype_id' => 'required|numeric|exists:App\Models\VehicleField,id,key,BODY_TYPE',
            'accessories.*' => 'nullable|exists:App\Models\VehicleField,id,key,ACCESSORY',
            'value' => 'required|numeric',
            'city_id' => 'required|numeric|exists:App\Models\City,id',
            'description' => 'required|max:500',
            'delivery' => 'required|boolean',
            'warranty' => 'required|boolean',
            'armored' => 'required|boolean',
            'only_owner' => 'required|boolean',
            'seven_places' => 'required|boolean',
            'review' => 'required|boolean',
            'spotlight' => 'sometimes|in:N,S,G,D',
        ],
        'update-spotlight' => [
            'id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric|exists:App\Models\Publisher,id',
            'spotlight' => 'required|in:N,S,G,D',
        ],
        'destroy' => [
            'id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric',
            'reason' => 'required|string',
        ],
    ];

    private array $messages = [
        'plate.unique' => 'O campo placa já está sendo utilizado.',
    ];

    public function __construct(VehicleService $service)
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
    public function search(Request $request): JsonResponse
    {
        return $this->service->searchExternal(['publisher_id' => $this->publisher_id, 'all' => true, 'active' => true]);
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param $id
     * @return JsonResponse
     * @throws CustomException
     */
    public function show($id): JsonResponse
    {
        return $this->service->showExternal($id, $this->publisher_id);
    }

    /**
     * Store resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        $data = array_merge($request->all(), ["publisher_id" => $this->publisher_id, 'origin' => 'external']);

        $rules = $this->validator['store'];
        $rules['plate'] = 'required|regex:/[A-Za-z]{3}[0-9][0-9A-Za-z][0-9]{2}/|unique:vehicles,plate,NULL,id,deleted_at,NULL,publisher_id,' . $this->publisher_id;

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        try {
            $resource = $this->service->store($validator->validate(), false);
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
     * @throws CustomException
     * @throws ValidationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rules = $this->validator['update'];
        $rules['id'] .= ',publisher_id,' . $this->publisher_id;
        $rules['plate'] = 'required|regex:/[A-Za-z]{3}[0-9][0-9A-Za-z][0-9]{2}/|unique:vehicles,plate,' . $id . ',id,deleted_at,NULL,publisher_id,' . $this->publisher_id;

        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id, "publisher_id" => $this->publisher_id]),
            $rules,
            $this->messages
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }
        try {
            $resource = $this->service->update($validator->validate(), false);
            return Helpers::reponse(true, $this->service->makeHidenExternal($resource), 200);
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
     * @throws Exception
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id, "publisher_id" => $this->publisher_id]),
            $this->validator['destroy'],
            $this->messages
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        try {
            $resource = $this->service->destroy($validator->validated(), false, true);
            return Helpers::reponse(true, $this->service->makeHidenExternal($resource), 200);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }
}
