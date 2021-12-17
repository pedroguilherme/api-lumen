<?php

namespace App\Http\Controllers\V1\External;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    private PublisherService $service;
    private int $publisher_id;

    public function __construct(PublisherService $service)
    {
        $this->service = $service;
        $this->publisher_id = Auth::user()->publisher_id;
    }

    /**
     * @return JsonResponse
     * @throws CustomException
     */
    public function show(): JsonResponse
    {
        $showPlan = $this->service->showPlan(['id' => $this->publisher_id], false);

        return Helpers::reponse(true, [
            'name' => ($showPlan->name ?? 'Not Found'),
            'normal' => ($showPlan->normal ?? 'Not Found'),
            'silver' => ($showPlan->silver ?? 'Not Found'),
            'gold' => ($showPlan->gold ?? 'Not Found'),
            'diamond' => ($showPlan->diamond ?? 'Not Found'),
            'available' => ($showPlan->available ?? 'Not Found'),
        ]);
    }
}
