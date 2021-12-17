<?php

namespace App\Traits;

use App\Contracts\DefaultModelContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use \Exception;

trait DefaultService
{
    protected DefaultModelContracts $model;

    public function __construct(DefaultModelContracts $model)
    {
        $this->model = $model;
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function search($data, $jsonResponse = true)
    {
        try {
            //Apply filters
            if (isset($data["all"]) && $data["all"] == "true") {
                $resources = $this->model->applyShowWith($data)->get();
            } else {
                $resources = $this->model->applyShowWith($data)->paginate(Config::get('constant.pagination'));
            }

            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function show($data, $jsonResponse = true)
    {
        try {
            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource store a database.
     * Check if resource created exist actually, if exist return error with resource
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function store($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Check if resource created exist actually
            $resource = $this->model->applyShowWith($data)->firstOrNew($data);

            if (isset($resource->id)) {
                return Helpers::reponse(false, $resource, 406, Config::get('errors.duplicate'), $jsonResponse);
            }

            if ($resource->save()) {
                DB::commit();
                return Helpers::reponse(true, $this->show($resource, false), 201, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao gravar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource update a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function update($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $resource->fill($data);

            if ($resource->save()) {
                DB::commit();
                return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource destroy or restore a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean
     * @throws Exception
     */
    public function destroy($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            if ($resource->trashed()) {
                $result = $resource->restore();
            } else {
                $result = $resource->delete();
            }

            if ($result) {
                DB::commit();
                return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
            } else {
                throw new Exception('Falha ao atualizar no banco de dados');
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
