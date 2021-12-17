<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersImage;
use App\Models\Brand;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class BrandService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(Brand $model)
    {
        $this->model = $model;
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
            if (isset($data["file"]) && !empty($data["file"])) {
                $path = HelpersImage::upload(Image::make($data["file"]), 'brand_image');
            }

            DB::beginTransaction();

            // Check if resource created exist actually
            $resource = $this->model->applyShowWith($data)->firstOrNew(Arr::only($data, $this->model->getFillable()));

            if (isset($resource->id)) {
                return Helpers::reponse(false, $resource, 406, Config::get('errors.duplicate'), $jsonResponse);
            }

            // Insere o caminho da imagem
            $resource->path = ($path ?? null);

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
            if (isset($data["file"])) {
                if (!empty($data["file"])) {
                    $path = HelpersImage::upload(Image::make($data["file"]), 'brand_image');
                } else {
                    $path = "";
                }
            }

            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith()->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            $resource->fill($data);

            // Atualiza a Imagem
            if (isset($path)) {
                $resource->path = (empty($path) ? null : $path);
            }

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
                ->makeHidden(['url']);
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
            $result = DB::table('brands as b')
                ->select('b.id', 'b.name as label', 'b.vehicle_type', 'b.path', DB::raw('count(v.id) as count'))
                ->join('vehicles as v', 'v.brand_id', '=', 'b.id')
                ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
                ->where(function ($query) {
                    $query->orWhereIn('p.payment_situation', ['paid', 'free'])
                        ->orWhere('v.payment_status', 'paid');
                })
                ->whereNull('v.deleted_at')
                ->whereNull('b.deleted_at')
                ->groupBy('b.vehicle_type', 'b.id', 'b.name', 'b.path')
                ->orderBy('b.vehicle_type')
                ->orderBy(DB::raw('count(v.id)'), 'desc');

            if (isset($data['vehicle_type']) && !empty($data['vehicle_type'])) {
                $result = $result->where('b.vehicle_type', '=', $data['vehicle_type']);
            }

            $result = $result->get();

            $result->transform(function ($object) {
                $object->url = !empty($object->path) ? Storage::url($object->path) : null;
                return $object;
            });

            $result = $result->groupBy('vehicle_type');


            return Helpers::reponse(true, $result, 200, [], true);
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
