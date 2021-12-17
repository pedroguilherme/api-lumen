<?php

namespace App\Http\Controllers\V1\Site\SignUp;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Traits\DefaultController;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Request as RequestFacade;

class RegisterController extends Controller
{
    use ValidateCpfCnpj;

    private PublisherService $service;

    private array $validator = [
        'store' => [
            'type' => 'required|in:J,F',
            'name' => 'required|string',
            'company_name' => 'required_if:type,J|string|nullable',
            'cpf_cnpj' => 'required|string|unique:publishers,cpf_cnpj',
            'cep' => 'required|string',
            'number' => 'required|string',
            'neighborhood' => 'required|string',
            'address' => 'required|string',
            'complement' => 'string|nullable',
            'logo' => 'string|nullable',
            'description' => 'string|nullable',
            'work_schedule' => 'string|nullable',
            'plan_id' => 'required_if:type,J|nullable|numeric',
            'city_id' => 'required|numeric',
            'login' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:15|string',
            'contacts.*.key' => 'string|nullable',
            'contacts.*.value' => 'string|nullable',
            'payment_method' => 'required_if:type,J|in:CC,BOL',
            'cc_holder_name' => 'required_if:payment_method,CC|string|nullable',
            'cc_number' => 'required_if:payment_method,CC|numeric|nullable',
            'cc_expiration_date' => 'required_if:payment_method,CC|date_format:m/Y|nullable',
            'cc_cvv' => 'required_if:payment_method,CC|numeric|nullable',
        ]
    ];

    public function __construct(PublisherService $service)
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), (($this->validator['store'] ?? $this->validator) ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();

        $validator = $this->validateCpfCnpj($data);

        $data['origin'] = 'site';

        if ($validator !== true) {
            return Helpers::reponse(false, [], 406, $validator);
        } else {
            return $this->service->store($data);
        }
    }
}
