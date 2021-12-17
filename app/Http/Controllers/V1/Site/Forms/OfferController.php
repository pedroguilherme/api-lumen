<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\OfferService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OfferController extends Controller
{
    private array $rules = [
        'vehicle' => [
            'publisher_id' => 'required|numeric',
            'vehicle_id' => 'required|numeric',
            'client_name' => 'required|string',
            'client_contact' => 'required|string',
            'client_email' => 'required|email|string',
        ],
        'sellVehicle' => [
            'client_name' => 'required|string',
            'client_contact' => 'required|string',
            'client_email' => 'required|email|string',
            'client_car_version_id' => 'required|numeric',
            'client_car_year_manufacture' => 'required|string',
            'client_car_year_model' => 'required|string',
            'client_car_details' => 'required|string|max:255',
            'client_car_discount' => 'required|string|max:255',
        ],
        'banner' => [
            'publisher_id' => 'required|numeric',
            'client_name' => 'required|string',
            'client_contact' => 'required|string',
        ],
    ];

    private array $messages = [
        'client_init_message.required' => 'O campo mensagem é obrigatório.',
    ];

    private OfferService $service;

    /**
     * OfferController constructor.
     * @param OfferService $service
     */
    public function __construct(OfferService $service)
    {
        $this->service = $service;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function financing(Request $request)
    {
        $validator = Validator::make($request->all(),
            array_merge($this->rules['vehicle'], ['client_init_message' => 'required|string']),
            $this->messages);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->financing($validator->validated());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function proposal(Request $request)
    {
        $validator = Validator::make($request->all(),
            array_merge($this->rules['vehicle'], ['client_init_message' => 'required|string']),
            $this->messages);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->proposal($validator->validated());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function seePhone(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules['vehicle']);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->seePhone($validator->validated());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function sellVehicle(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules['sellVehicle']);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->sellVehicle($validator->validated());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function banner(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules['banner']);

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->banner($validator->validated());
    }
}
