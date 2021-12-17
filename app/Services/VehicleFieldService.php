<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\VehicleField;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class VehicleFieldService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(VehicleField $model)
    {
        $this->model = $model;
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
                if ($resource->vehiclesFuels->count() > 0 ||
                    $resource->vehiclesTransmissions->count() > 0 ||
                    $resource->vehiclesColors->count() > 0 ||
                    $resource->vehiclesBodyType->count() > 0 ||
                    $resource->vehiclesAccessories->count() > 0) {
                    return Helpers::reponse(false, [], 406, Config::get('errors.in_use'), $jsonResponse);
                }

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

    /**
     * @param $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function searchExternal($data): JsonResponse
    {
        try {
            $resources = $this->model->applyShowWith($data)->select('id', 'value')->get();
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
            $relation = null;
            switch ($data['key']) {
                case 'BODY_TYPE':
                    $relation = 'v.bodytype_id';
                    break;
                case 'COLOR':
                    $relation = 'v.color_id';
                    break;
                case 'FUEL':
                    $relation = 'v.fuel_id';
                    break;
                case 'TRANSMISSION':
                    $relation = 'v.transmission_id';
                    break;
            }

            if (!empty($relation)) {
                $result = DB::table('vehicle_fields as vf')
                    ->select('vf.id', 'vf.value as label', 'vf.vehicle_type', 'vf.key', DB::raw('count(v.id) as count'))
                    ->join('vehicles as v', $relation, '=', 'vf.id')
                    ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
                    ->where(function ($query) {
                        $query->orWhereIn('p.payment_situation', ['paid', 'free'])
                            ->orWhere('v.payment_status', 'paid');
                    })
                    ->whereNull('v.deleted_at')
                    ->whereNull('vf.deleted_at')
                    ->groupBy('vf.id', 'vf.vehicle_type', 'vf.key', 'vf.value')
                    ->orderBy('vf.vehicle_type')
                    ->orderBy('vf.value')
                    ->orderBy(DB::raw('count(v.id)'), 'desc');

                if (isset($data['vehicle_type']) && !empty($data['vehicle_type'])) {
                    $result = $result->where('vf.vehicle_type', '=', $data['vehicle_type']);
                }

                if (isset($data['key']) && !empty($data['key'])) {
                    $result = $result->where('vf.key', '=', $data['key']);
                }

                $result = $result->get()->groupBy('vehicle_type');

                return Helpers::reponse(true, $result, 200, [], true);
            } else {
                throw new Exception('Campo KEY com valor inválido (BODY_TYPE, COLOR, FUEL, TRANSMISSION)');
            }
        } catch (Exception $e) {
            $errors = ['type' => 'VALIDATOR', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }
}
