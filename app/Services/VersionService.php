<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Models\Version;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class VersionService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(Version $model)
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
                ->get()
                ->makeHidden(['model']);
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
    public function searchSite($data)
    {
        try {
            $result = DB::table('versions as vs')
                ->select('vs.id', 'vs.name as label', 'vs.model_id', DB::raw('count(v.id) as count'))
                ->join('vehicles as v', 'v.version_id', '=', 'vs.id')
                ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
                ->where(function ($query) {
                    $query->orWhereIn('p.payment_situation', ['paid', 'free'])
                        ->orWhere('v.payment_status', 'paid');
                })
                ->whereNull('v.deleted_at')
                ->whereNull('vs.deleted_at')
                ->where('vs.model_id', '=', $data['model_id'])
                ->groupBy('vs.model_id', 'vs.id', 'vs.name')
                ->orderBy('vs.model_id')
                ->orderBy('vs.name')
                ->orderBy(DB::raw('count(v.id)'), 'desc');

            return Helpers::reponse(true, $result->get(), 200, [], true);
        } catch (\Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * Resource destroy or restore a database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
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
                if ($resource->vehicles->count() > 0) {
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
}
