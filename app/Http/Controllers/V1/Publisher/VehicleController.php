<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Services\PublisherService;
use App\Services\VehicleService;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    protected VehicleService $service;

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
            'description' => 'required',
            'delivery' => 'required|boolean',
            'warranty' => 'required|boolean',
            'armored' => 'required|boolean',
            'only_owner' => 'required|boolean',
            'seven_places' => 'required|boolean',
            'review' => 'required|boolean',
            'spotlight' => 'required|in:N,S,G,D',
            'images.*.id' => 'numeric|nullable',
            'images.*.order' => 'numeric|nullable'
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
            'description' => 'required',
            'delivery' => 'required|boolean',
            'warranty' => 'required|boolean',
            'armored' => 'required|boolean',
            'only_owner' => 'required|boolean',
            'seven_places' => 'required|boolean',
            'review' => 'required|boolean',
            'spotlight' => 'sometimes|in:N,S,G,D',
            'images.*.id' => 'numeric|nullable',
            'images.*.order' => 'numeric|nullable',
        ],
        'update-spotlight' => [
            'id' => 'required|numeric|exists:App\Models\Vehicle,id',
            'publisher_id' => 'required|numeric|exists:App\Models\Publisher,id',
            'spotlight' => 'required|in:N,S,G,D',
        ],
        'destroy' => [
            'id' => 'required|numeric',
            'publisher_id' => 'required|numeric',
            'reason' => 'required|string',
        ],
    ];

    private array $messages = [
        'plate.unique' => 'O campo placa já está sendo utilizado.',
    ];

    private int $publisher_id;
    private Publisher $publsher;

    public function __construct(VehicleService $service)
    {
        $this->service = $service;
        $this->publisher_id = Auth::user()->publisher_id;
        $this->publsher = Auth::user()->publisher;
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
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function showByToken(int $id)
    {
        return $this->service->show(['id' => $id, 'publisher_id' => $this->publisher_id]);
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
        $rules = $this->validator['store'];

        if ($this->publsher->type == 'F') {
            $rules['plan'] = 'required|string|in:basic,intermediary,advanced';
        }

        $rules['plate'] = 'required|regex:/[A-Za-z]{3}[0-9][0-9A-Za-z][0-9]{2}/|unique:vehicles,plate,NULL,id,deleted_at,NULL,publisher_id,' . $this->publisher_id;

        $validator = Validator::make(
            array_merge($request->all(), ["publisher_id" => $this->publisher_id]),
            $rules,
            $this->messages
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
     * @param $id
     * @return JsonResponse
     * @throws CustomException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateByToken(Request $request, $id)
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

        return $this->service->update($validator->validated(), true);
    }

    /**
     * UpdateSpotLight resource by Auth Publisher
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws CustomException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateSpotlightByToken(Request $request, $id)
    {
        $rules = $this->validator['update-spotlight'];
        $rules['id'] .= ',publisher_id,' . $this->publisher_id;

        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id, "publisher_id" => $this->publisher_id]),
            $rules,
            $this->messages
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->update($validator->validated(), true, 'spotlight');
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
        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id, "publisher_id" => $this->publisher_id]),
            $this->validator['destroy'],
            $this->messages
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();

        return $this->service->destroy($data);
    }
}
