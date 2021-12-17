<?php

namespace App\Http\Controllers\V1\Admin\Site;

use App\Contracts\DefaultControllerContracts;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\SiteContactService;
use App\Traits\DefaultController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SiteContactController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
        'update' => [
            '*.key' => 'required|string',
            '*.value' => 'nullable|string',
        ],
        'destroy' => [
            'id' => 'required|numeric',
        ]
    ];

    public function __construct(SiteContactService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
    }

    /**
     * Update the specified resource in storage.
     *
     * @overwrites
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Exception
     */
    public function update(Request $request, $id = null)
    {
        $validator = Validator::make(
            $request->all(),
            (($this->validator['update'] ?? $this->validator) ?? [])
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->update($validator->validated());
    }
}
