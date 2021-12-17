<?php

namespace App\Http\Controllers\V1\Site\Logs;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\EventService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    private EventService $service;

    /**
     * OfferController constructor.
     * @param EventService $service
     */
    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws ValidationException
     * @throws Exception
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'type' => 'required|string|in:VEHICLE_WHATSAPP,VEHICLE_VIEW,VEHICLE_PHONE,PARTNERS_WHATSAPP,PARTNERS_PHONE,PARTNERS_VIEW,BANNER_CLICK',
                'publisher_id' => 'required|numeric',
                'vehicle_id' => 'required_if:type,VEHICLE_WHATSAPP,VEHICLE_PHONE,VEHICLE_VIEW|numeric|nullable'
            ]
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->store($validator->validated());
    }
}
