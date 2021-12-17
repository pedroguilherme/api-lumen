<?php

namespace App\Http\Controllers\V1\Publisher;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Services\PublisherService;
use App\Traits\ValidateCpfCnpj;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    use ValidateCpfCnpj;

    protected DefaultServiceContracts $service;

    private array $validator = [
        'update_plan' => [
            'id' => 'required|numeric',
            'plan_id' => 'required|numeric',
        ],
        'update' => [
            'type' => 'required|in:J,F',
            'name' => 'required|string',
            'company_name' => 'required_if:type,J|string|nullable',
            'cpf_cnpj' => 'required|string|unique:publishers,cpf_cnpj',
            'cep' => 'required|string',
            'number' => 'required|string',
            'neighborhood' => 'required|string',
            'address' => 'required|string',
            'complement' => 'string|nullable',
            'description' => 'string|nullable',
            'logo' => 'string|nullable',
            'work_schedule' => 'string|nullable',
            'city_id' => 'required|numeric',
            'login' => 'required|email|unique:users,email',
            'password' => 'nullable|min:6|max:15|confirmed',
            'password_confirmation' => 'required_with:password|nullable|min:6|max:15',
            'contacts.*.key' => 'string|nullable',
            'contacts.*.value' => 'string|nullable',
        ],
        'destroy' => [
            'id' => 'required|numeric',
            'reason' => 'required|string',
        ],
    ];

    private int $publisher_id;

    public function __construct(PublisherService $service)
    {
        $this->service = $service;
        $this->publisher_id = Auth::user()->publisher_id;
    }

    /**
     * Display a resource by Auth Publisher
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function showByToken()
    {
        return $this->service->show(['id' => $this->publisher_id]);
    }

    /**
     * Update resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updateByToken(Request $request)
    {
        $this->validator['update']['cpf_cnpj'] = 'required|string|unique:publishers,cpf_cnpj,' . $this->publisher_id;
        $this->validator['update']['login'] = 'required|email|unique:users,email,' . $this->publisher_id . ',publisher_id';

        $validator = Validator::make(
            $request->all(),
            $this->validator['update']
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();
        $data['id'] = $this->publisher_id;
        $validator = $this->validateCpfCnpj($data);

        if ($validator !== true) {
            return Helpers::reponse(false, [], 406, $validator);
        } else {
            return $this->service->update($data);
        }
    }

    /**
     * Destroy a resource by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws Exception
     */
    public function destroyByToken(Request $request)
    {
        $validator = Validator::make(
            array_merge($request->all(), ["id" => $this->publisher_id]),
            $this->validator['destroy']
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        $data = $validator->validated();
        $data['origin'] = 'publisher';

        return $this->service->destroy($data);
    }

    /**
     * Show Plan Info Publisher by Auth Publisher
     *
     * @return JsonResponse
     * @throws CustomException
     */
    public function showPlanByToken()
    {
        return $this->service->showPlan(['id' => $this->publisher_id]);
    }

    /**
     * Show Plan Info Publisher by Auth Publisher
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function updatePlanByToken(Request $request)
    {
        $validator = Validator::make(
            array_merge($request->all(), ["id" => $this->publisher_id]),
            $this->validator['update_plan']
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->updatePlan($validator->validated());
    }
}
