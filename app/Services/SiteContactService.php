<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\SiteContact;
use App\Traits\DefaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;

class SiteContactService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(SiteContact $model)
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
            $resources = $this->model->applyShowWith($data)->get()->pluck("value", "key");

            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource update a database.
     *
     * @overwrites
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|boolean|array
     * @throws Exception
     */
    public function update($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $resources = [];
            foreach ($data as $contact) {
                $resource = $this->model::where('key', $contact["key"])->first();

                if (empty($resource)) {
                    return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
                }

                $resource->fill($contact);

                if (!$resource->save()) {
                    throw new Exception('Falha ao atualizar no banco de dados');
                }
                array_push($resources, $resource);
            }

            DB::commit();
            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
