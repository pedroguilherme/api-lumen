<?php

namespace App\Traits;

use App\Contracts\DefaultServiceContracts;
use App\Helpers\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait DefaultController
{
    protected DefaultServiceContracts $service;

    protected array $validator = [];

    /**
     * DefaultController constructor.
     * @param DefaultServiceContracts $service
     */
    public function __construct(DefaultServiceContracts $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        return $this->service->search($request->all());
    }

    /**
     * Display a resource of the id
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request, int $id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id]),
            (($this->validator['show'] ?? $this->validator) ?? [])
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->show($validator->validated());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), (($this->validator['store'] ?? $this->validator) ?? []));

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->store($validator->validated());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ["id" => $id]),
            (($this->validator['update'] ?? $this->validator) ?? [])
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->update($validator->validated());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function destroy(int $id)
    {
        $validator = Validator::make(
            ["id" => $id],
            (($this->validator['destroy'] ?? $this->validator) ?? [])
        );

        if ($validator->fails()) {
            return Helpers::reponseWithValidationError($validator->errors());
        }

        return $this->service->destroy($validator->validated());
    }
}
