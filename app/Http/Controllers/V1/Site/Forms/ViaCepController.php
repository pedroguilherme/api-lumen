<?php

namespace App\Http\Controllers\V1\Site\Forms;

use App\Contracts\DefaultControllerContracts;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\ViaCepService;
use App\Traits\DefaultController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ViaCepController extends Controller implements DefaultControllerContracts
{
    use DefaultController;

    private array $rulesValidator = [
        'search' => [
            'cep' => 'required|digits:8',
        ],
    ];

    public function __construct(ViaCepService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), (($this->rulesValidator['search'] ?? $this->rulesValidator) ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->search($validator->validated());
    }
}
