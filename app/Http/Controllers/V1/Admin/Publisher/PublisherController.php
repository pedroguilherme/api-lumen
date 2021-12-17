<?php

namespace App\Http\Controllers\V1\Admin\Publisher;

use App\Exceptions\CustomException;
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

class PublisherController extends Controller
{
    use DefaultController;
    use ValidateCpfCnpj;

    private array $rulesValidator = [
        'show' => [
            'id' => 'required|numeric',
        ],
        'store' => [
            'origin' => 'required|in:admin',
            'type' => 'required|in:J,F',
            'name' => 'required|string',
            'company_name' => 'required_if:type,J|string|nullable',
            'cpf_cnpj' => 'required|string|unique:publishers,cpf_cnpj',
            'cep' => 'required|max:255|string',
            'number' => 'required|max:255|string',
            'neighborhood' => 'required|max:255|string',
            'address' => 'required|max:255|string',
            'complement' => 'string|max:255|nullable',
            'description' => 'string|max:255|nullable',
            'logo' => 'string|nullable',
            'work_schedule' => 'string|nullable',
            'plan_id' => 'required_if:type,J|numeric',
            'city_id' => 'required|numeric',
            'login' => 'required|email|unique:users,email',
            'password' => 'required_if:origin,site|min:6|max:15|string|nullable',
            'free' => 'required_with:free_active_date|numeric|nullable',
            'free_active_date' => 'required_with:free|date_format:d/m/Y|nullable',
            'contacts.*.key' => 'string|nullable',
            'contacts.*.value' => 'string|nullable',
        ],
        'update' => [
            'id' => 'required|numeric',
            'type' => 'required|in:J,F',
            'name' => 'required|string',
            'company_name' => 'required_if:type,J|string|nullable',
            'cpf_cnpj' => 'required|string|unique:publishers,cpf_cnpj',
            'cep' => 'required|max:255|string',
            'number' => 'required|max:255|string',
            'neighborhood' => 'required|max:255|string',
            'address' => 'required|max:255|string',
            'complement' => 'string|max:255|nullable',
            'description' => 'string|max:255|nullable',
            'logo' => 'string|nullable',
            'work_schedule' => 'string|nullable',
            'plan_id' => 'required_if:type,J|numeric',
            'city_id' => 'required|numeric',
            'login' => 'required|email|unique:users,email',
            'password' => 'required_if:origin,site|min:6|max:15|string|nullable',
            'free' => 'numeric|nullable',
            'free_active_date' => 'date_format:d/m/Y|nullable',
            'contacts.*.key' => 'string|nullable',
            'contacts.*.value' => 'string|nullable',
        ],
        'destroy' => [
            'id' => 'required|numeric',
            'reason' => 'required|string',
        ]
    ];

    public function __construct(PublisherService $service)
    {
        $this->service = $service;
        $this->validator = $this->rulesValidator;
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

        if ($validator !== true) {
            return Helpers::reponse(false, [], 406, $validator);
        } else {
            return $this->service->store($data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws CustomException
     */
    public function update(Request $request, int $id)
    {
        $rules = (($this->validator['update'] ?? $this->validator) ?? []);

        $rules['cpf_cnpj'] = 'required|string|unique:publishers,cpf_cnpj,' . $id;
        $rules['login'] = 'required|email|unique:users,email,' . $id . ',publisher_id';

        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id]),
            $rules
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();

        $validator = $this->validateCpfCnpj($data);

        if ($validator !== true) {
            return Helpers::reponse(false, [], 406, $validator);
        } else {
            return $this->service->update($data);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function destroy(int $id)
    {
        $validator = Validator::make(
            array_merge(RequestFacade::all(), ["id" => $id]),
            (($this->validator['destroy'] ?? $this->validator) ?? [])
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->destroy($validator->validated());
    }
}
