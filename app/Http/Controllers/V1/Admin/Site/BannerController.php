<?php

namespace App\Http\Controllers\V1\Admin\Site;

use App\Contracts\DefaultControllerContracts;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\BannerService;
use App\Traits\DefaultController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
        'store' => [
            'platform' => 'required|in:desktop,mobile,all',
            'key' => 'required|in:super,category',
            'link' => 'required|string',
            'title' => 'required|string',
            'order' => 'required|string',
            'file' => 'required|string',
        ],
        'update' => [
            'id' => 'required|numeric',
            'link' => 'nullable|string',
            'title' => 'nullable|string'
        ],
        'order' => [
            '*.id' => 'required|numeric',
            '*.order' => 'required|numeric',
        ],
        'destroy' => [
            'id' => 'required|numeric',
        ]
    ];

    public function __construct(BannerService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }


    /**
     * Update Order off all Resources
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function orders(Request $request)
    {
        $validator = Validator::make($request->all(), (($this->validator['order'] ?? $this->validator) ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->orders($validator->validated());
    }
}
