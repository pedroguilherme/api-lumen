<?php

namespace App\Services;

use App\Contracts\DefaultModelContracts;
use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersImage;
use App\Models\File;
use App\Models\Vehicle;
use App\Traits\DefaultService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class VehicleFileService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(File $model)
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
            $image = Image::make($data["file"]);

            if ($image->width() > 1280) {
                $image = $image->resize(1280, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }

            $path = HelpersImage::upload(
                $image,
                'vehicle_image',
                null,
                [
                    '$publisher_id' => $data['publisher_id'],
                    '$vehicle_id' => ($data['vehicle_id'] ?? 'temporary'),
                ]
            );

            if ($path !== false) {
                DB::beginTransaction();
                $resource = $this->model->fill($data);
                $resource->type = 'N';
                $resource->vehicle_id = ($data['vehicle_id'] ?? null);
                $resource->path = $path;
                if ($resource->save()) {
                    DB::commit();
                    return Helpers::reponse(true, $this->show($resource->toArray(), false), 201, [], $jsonResponse);
                } else {
                    throw new Exception('Falha ao gravar no banco de dados');
                }
            } else {
                throw new Exception('Falha ao gravar IMAGEM');
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
            foreach ($data["order"] as $order) {
                $resource = $this->model->applyShowWith(Arr::only($data, ["vehicle_id"]))->find($order["id"]);

                if (empty($resource)) {
                    return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
                }

                $resource->fill($order);

                if (!$resource->save()) {
                    throw new Exception('Falha ao atualizar no banco de dados');
                }
            }
            DB::commit();
            return Helpers::reponse(true, null, 200, [], $jsonResponse);
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
            $resource = $this->model->applyShowWith(Arr::only($data, ["vehicle_id"]))->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            if ($resource->forceDelete()) {
                if (Storage::delete($resource->path)) {
                    DB::commit();
                    return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
                } else {
                    throw new Exception('Falha ao excluir imagem');
                }
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
     * @param $resources
     * @return DefaultModelContracts|Vehicle|mixed
     */
    public function makeHidenExternal($resources)
    {
        return $resources->makeHidden([
            'type',
            'path',
            'vehicle_id',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);
    }
}
