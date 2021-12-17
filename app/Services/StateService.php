<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\State;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StateService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(State $model)
    {
        $this->model = $model;
    }

    /**
     * @return JsonResponse
     * @throws CustomException
     */
    public function searchExternal(): JsonResponse
    {
        try {
            $resources = $this->model
                ->select('uf', 'name')
                ->get();
            return Helpers::reponse(true, $resources, 200, [], true);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }
}
