<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\Plan;
use App\Traits\DefaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\DB;

class PlanService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(Plan $model)
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
                $resources = $this->model->applyShowWith($data)->get()->groupBy('type');
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
    public function pfPlans($data, $jsonResponse = true)
    {
        try {
            //Apply filters
            return Helpers::reponse(true, Config::get('constant.pf_plans'), 200, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
