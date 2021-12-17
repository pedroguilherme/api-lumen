<?php

namespace App\Services;

use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersImage;
use App\Models\Banner;
use App\Traits\DefaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;

class BannerService implements DefaultServiceContracts
{
    use DefaultService;

    public function __construct(Banner $model)
    {
        $this->model = $model;
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws Exception
     */
    public function search($data, $jsonResponse = true)
    {
        try {
            //Apply filters
            $data["active"] = true;
            $resources = $this->model->applyShowWith($data)->get()->groupBy("platform")->toArray();
            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
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
     * @return JsonResponse|Banner
     * @throws Exception
     */
    public function store($data, $jsonResponse = true)
    {
        try {
            $path = HelpersImage::upload(Image::make($data["file"]), 'banner_' . $data['key']);
            if ($path !== false) {
                DB::beginTransaction();
                $resource = new Banner();
                $resource->fill($data);
                $resource->path = $path;
                if ($resource->save()) {
                    DB::commit();
                    return Helpers::reponse(true, $this->show($resource, false), 201, [], $jsonResponse);
                } else {
                    Storage::delete($path);
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
     * Resource update order at database.
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse|Banner
     * @throws Exception
     */
    public function orders($data, $jsonResponse = true)
    {
        try {
            DB::beginTransaction();
            $resources = [];
            foreach ($data as $order) {
                $resource = $this->model->applyShowWith()->find($order["id"]);
                if (!empty($resource)) {
                    $resource->order = $order["order"];
                    if ($resource->save()) {
                        array_push($resources, $resource);
                    } else {
                        throw new Exception('Falha ao salvar objeto no banco de dados: ' . $order["id"]);
                    }
                } else {
                    throw new Exception('Falha ao encontrar objeto com o ID: ' . $order["id"]);
                }
            }
            DB::commit();
            return Helpers::reponse(true, $resources, 201, [], $jsonResponse);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }
}
