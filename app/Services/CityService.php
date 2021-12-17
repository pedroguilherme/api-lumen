<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\City;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class CityService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(City $model)
    {
        $this->model = $model;
    }

    /**
     * @param $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function searchExternal($data): JsonResponse
    {
        try {
            $resources = $this->model->applyShowWith($data)
                ->select('id', 'name')
                ->get();
            return Helpers::reponse(true, $resources, 200, [], true);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * @param $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function searchSite($data): JsonResponse
    {
        try {
            $result = DB::table('vehicles as v')
                ->select('c.id', 'c.name as city', 'c.state_id as uf', DB::raw('count(v.id) as count'))
                ->join('cities as c', 'c.id', '=', 'v.city_id')
                ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
                ->where(function ($query) {
                    $query->orWhereIn('p.payment_situation', ['paid', 'free'])
                        ->orWhere('v.payment_status', 'paid');
                })
                ->whereNull('v.deleted_at')
                ->whereNull('c.deleted_at')
                ->groupBy('c.id', 'c.name', 'c.state_id')
                ->orderBy(DB::raw('count(v.id)'), 'desc')
                ->orderBy('c.name');

            if (isset($data['state_id']) && !empty($data['state_id'])) {
                $result = $result->where('v.state_id', '=', $data['state_id']);
            }

            $result = $result->get();

            return Helpers::reponse(true, $result, 200, [], true);
        } catch (Exception $e) {
            $errors = ['type' => 'VALIDATOR', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }
}
