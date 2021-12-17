<?php

namespace App\Services;

use App\Contracts\DefaultModelContracts;
use App\Contracts\DefaultServiceContracts;
use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersSite;
use App\Models\File;
use App\Models\Offer;
use App\Models\Publisher;
use App\Models\Vehicle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehicleService
{
    private DefaultModelContracts $model;
    private PublisherService $publisherService;
    private VersionService $versionService;

    /**
     * VehicleService constructor.
     * @param Vehicle $model
     * @param PublisherService $publisherService
     * @param VersionService $versionService
     */
    public function __construct(Vehicle $model, PublisherService $publisherService, VersionService $versionService)
    {
        $this->model = $model;
        $this->publisherService = $publisherService;
        $this->versionService = $versionService;
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws Exception
     */
    public function search($data, bool $jsonResponse = true)
    {
        try {
            //Apply filters
            // Pega apenas veiculos ativos
            $data['active'] = true;
            if (isset($data["all"]) && $data["all"] == "true") {
                $resources = $this->model->applyShowWith($data)->get();
            } else {
                $resources = $this->model->applyShowWith($data)->paginate(Config::get('constant.pagination'));
            }

            if (isset($data['origin']) && $data['origin'] == 'token') {
                $publisher = Publisher::find($data['publisher_id']);
                $eventService = new EventService();
                if ($publisher->type == 'F') {
                    $chargeService = new ChargesService();
                    $resources->transform(function ($object) use ($chargeService, $publisher, $eventService) {
                        $object->can_reprocess = $chargeService->canReprocessPaymentPF($object, $publisher);
                        $object->leads = Offer::where('publisher_id', $object->publisher_id)
                            ->where('vehicle_id', $object->id)
                            ->count();
                        $object->clicks = $eventService->getClicksByVehicle($object->id);
                        return $object;
                    });
                    $resources->load('lastBilling');
                } else {
                    $resources->transform(function ($object) use ($eventService) {
                        $object->leads = Offer::where('publisher_id', $object->publisher_id)
                            ->where('vehicle_id', $object->id)
                            ->count();
                        $object->clicks = $eventService->getClicksByVehicle($object->id);
                        return $object;
                    });
                }
            }

            return Helpers::reponse(true, $resources, 200, [], $jsonResponse);
        } catch (Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, $jsonResponse, $e);
        }
    }

    /**
     * Resource search a database using filters
     *
     * @param $data
     * @param bool $jsonResponse
     * @return JsonResponse
     * @throws Exception
     */
    public function show($data, bool $jsonResponse = true)
    {
        try {
            // Get resource in database
            $resource = $this->model->applyShowWith($data)->find($data["id"]);

            if (empty($resource)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            return Helpers::reponse(true, $resource, 200, [], $jsonResponse);
        } catch (Exception $e) {
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
     * @return JsonResponse
     * @throws CustomException
     */
    public function store($data, bool $jsonResponse = true)
    {
        try {
            $publisher = Publisher::find($data["publisher_id"]);

            DB::beginTransaction();

            // Check Plans
            if ($publisher->type == 'J') {
                $plan = $this->publisherService->showPlan(["id" => $publisher->id], false);
                $available = $plan->available;
                $spotlight = $data["spotlight"];
                $configAbbreviations = Config::get('constant.spotlight_abbreviation');

                if ($available[$configAbbreviations[$spotlight]] <= 0) {
                    return Helpers::reponse(false, $data, 406, Config::get('errors.plan_used'), $jsonResponse);
                }
            }

            // Check if resource created exist actually
            $resource = $this->model->applyShowWith([
                'plate' => $data['plate']
            ])->firstOrNew(Arr::except($data, ['accessories', 'images', 'plan']));

            if (isset($resource->id)) {
                return Helpers::reponse(false, $resource, 406, Config::get('errors.duplicate_vehicle_plate'),
                    $jsonResponse);
            }

            // Get Brand / Model IDs

            $version = $this->versionService->show(["id" => $data["version_id"]], false);
            $resource->brand_id = $version->model->brand_id;
            $resource->model_id = $version->model->id;

            if ($resource->save()) {
                // Add accessories
                if (isset($data['accessories']) and is_array($data["accessories"])) {
                    foreach ($data['accessories'] as $accessory) {
                        $resource->accessories()->attach($accessory);
                    }
                }

                // Update Images Push
                if (isset($data['images']) and is_array($data["images"])) {
                    foreach ($data['images'] as $image) {
                        $file = File::find($image["id"]);
                        if (!empty($file)) {
                            // Change Path file
                            $newPath = str_replace('temporary', $resource->id, $file->path);
                            $result = Storage::disk('s3')->move($file->path, $newPath);
                            if ($result) {
                                $file->order = $image["order"];
                                $file->vehicle_id = $resource->id;
                                $file->path = $newPath;
                                if (!$file->save()) {
                                    throw new Exception('Falha ao gravar imagens no banco de dados');
                                }
                            } else {
                                throw new Exception('Falha ao mover IMAGEM para o diretorio');
                            }
                        }
                    }
                }

                // Se o publisher por PF
                if ($publisher->type == 'F') {
                    if (!empty($publisher->payment_method)) {
                        $chargeService = new ChargesService();
                        $plan = Config::get('constant.pf_plans.' . $data['plan']);

                        if (empty($plan)) {
                            return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
                        }

                        $resource->payment_status = $chargeService->makeInvoice($publisher, null, $plan, $resource);
                        $resource->disable_on = empty($plan['expiration']) ? null : Carbon::now()->addDays($plan['expiration']);
                    } else {
                        $resource->payment_status = 'first';
                    }
                    $resource->save();
                }

                DB::commit();
                return Helpers::reponse(true, $this->show($resource->toArray(), false), 201, [],
                    $jsonResponse);
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
     * @param string|null $action
     * @return JsonResponse|boolean
     * @throws CustomException
     */
    public function update($data, bool $jsonResponse = true, string $action = null)
    {
        try {
            $publisher = Publisher::find($data["publisher_id"]);

            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith(Arr::only($data, ['publisher_id']))->find($data["id"]);

            if (empty($resource) || !empty($resource->deleted_at)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            if ($publisher->type == 'J') {
                // Check Plans
                $configAbbreviations = Config::get('constant.spotlight_abbreviation');
                $plan = $this->publisherService->showPlan(["id" => $data["publisher_id"]], false);
                $available = $plan->available;
                // Remove 1 para o mesmo spotlight
                $available[$configAbbreviations[$resource->spotlight]] = $available[$configAbbreviations[$resource->spotlight]] + 1;
                $spotlight = $data["spotlight"];

                if ($available[$configAbbreviations[$spotlight]] <= 0) {
                    return Helpers::reponse(false, $data, 406, Config::get('errors.plan_used'), $jsonResponse);
                }
            }

            $resource->fill($data);

            if (isset($data["version_id"])) {
                $version = $this->versionService->show(["id" => $data["version_id"]], false);
                $resource->brand_id = $version->model->brand_id;
                $resource->model_id = $version->model->id;
            }

            if ($resource->save()) {
                // Update accessories
                if ($action != 'spotlight') {
                    $resource->accessories()->detach();
                    if (isset($data['accessories']) and is_array($data["accessories"])) {
                        foreach ($data['accessories'] as $accessory) {
                            $resource->accessories()->attach($accessory);
                        }
                    }
                }

                DB::commit();
                return Helpers::reponse(true, $this->show($resource->toArray(), false), 200, [],
                    $jsonResponse);
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
     * @param bool $check
     * @return JsonResponse
     * @throws CustomException
     */
    public function destroy($data, bool $jsonResponse = true, bool $check = false)
    {
        try {
            DB::beginTransaction();

            // Get resource in database
            $resource = $this->model->applyShowWith(Arr::only($data, ["publisher_id"]))->find($data["id"]);

            if (empty($resource)) {
                DB::rollBack();
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
            }

            if ($resource->trashed()) {
                if ($check) {
                    DB::rollBack();
                    return Helpers::reponse(false, [], 406, Config::get('errors.not_found'), $jsonResponse);
                } else {
                    $result = $resource->restore();
                }
            } else {
                $resource->deleted_reason = $data['reason'];
                if ($resource->save()) {
                    $result = $resource->delete();
                } else {
                    throw new Exception('Falha ao atualizar Motivo no banco de dados');
                }
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
    public function searchYearsSite($data): JsonResponse
    {
        try {
            $result = DB::table('vehicles as v')
                ->select(DB::raw('max(v.year_model) as max'), DB::raw('min(v.year_model) as min'))
                ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
                ->whereIn('p.payment_situation', ['paid', 'free'])
                ->whereNull('v.deleted_at');

            $result = $result->first();

            return Helpers::reponse(true, $result, 200, [], true);
        } catch (Exception $e) {
            $errors = ['type' => 'VALIDATOR', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * @param $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function searchPricesSite($data): JsonResponse
    {
        try {
            $result = DB::table('vehicles as v')
                ->select(DB::raw('max(v.value) as max'), DB::raw('min(v.value) as min'))
                ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
                ->whereIn('p.payment_situation', ['paid', 'free'])
                ->whereNull('v.deleted_at');

            $result = $result->first();

            return Helpers::reponse(true, $result, 200, [], true);
        } catch (Exception $e) {
            $errors = ['type' => 'VALIDATOR', 'data' => $e->getMessage()];
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * Resource search a database using filters
     *
     * @param int $vehicle
     * @return JsonResponse
     * @throws CustomException
     */
    public function showSite(int $vehicle): JsonResponse
    {
        try {
            $vehicle = $this->model->newQuery()->withTrashed()->find($vehicle);
            if (!empty($vehicle)) {
                if (empty($vehicle->deleted_at)) {
                    $vehicle->load([
                        'publisher.emails',
                        'publisher.phones',
                        'city',
                        'accessories',
                        'fuel',
                        'transmission',
                        'color',
                        'bodyType',
                        'version',
                        'model',
                        'brand',
                        'images',
                    ]);

                    return Helpers::reponse(true, [
                        "vehicle" => $vehicle,
                        "recommended" => $this->listRecommended($vehicle->id, $vehicle->type, $vehicle->brand_id,
                            $vehicle->model_id,
                            $vehicle->bodytype_id, $vehicle->value)
                    ]);
                } else {
                    return Helpers::reponse(false, [], 406, Config::get('errors.sold_vehicle'));
                }
            } else {
                return Helpers::reponse(false, [], 404, Config::get('errors.not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * @param $vehicleId
     * @param $type
     * @param $brandId
     * @param $modelId
     * @param $bodyType
     * @param $value
     * @return Collection
     */
    public function listRecommended($vehicleId, $type, $brandId, $modelId, $bodyType, $value): Collection
    {
        $result = DB::table('vehicles as v')
            ->select('v.id', 'v.type', 'v.plate', 'v.year_manufacture', 'v.year_model', 'v.mileage', 'v.doors',
                'v.value', 'v.description', 'v.delivery', 'v.ipva_paid', 'v.warranty', 'v.armored', 'v.only_owner',
                'v.seven_places', 'v.review', 'v.publisher_id', 'b.name as brand', 'v.brand_id', 'm.name as model',
                'v.model_id',
                'vs.name as version', 'v.version_id', 'vt.value as transmission', 'vf.value as fuel',
                'vc.value as color',
                'vb.value as bodytype', 'c.name as city', 'c.state_id as uf', 'f.path as image')
            ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
            ->join('brands as b', 'b.id', '=', 'v.brand_id')
            ->join('models as m', 'm.id', '=', 'v.model_id')
            ->join('versions as vs', 'vs.id', '=', 'v.version_id')
            ->leftJoin('files as f', function ($join) {
                $join->on('v.id', '=', 'f.vehicle_id')
                    ->where('f.order', '=', 0);
            })
            ->join('vehicle_fields as vt', 'v.transmission_id', '=', 'vt.id')
            ->join('vehicle_fields as vf', 'v.fuel_id', '=', 'vf.id')
            ->join('vehicle_fields as vc', 'v.color_id', '=', 'vc.id')
            ->join('vehicle_fields as vb', 'v.bodytype_id', '=', 'vb.id')
            ->join('cities as c', 'v.city_id', '=', 'c.id')
            ->where(function ($query) {
                $query->orWhereIn('p.payment_situation', ['paid', 'free'])
                    ->orWhere('v.payment_status', 'paid');
            })
            ->whereNull('v.deleted_at')
            ->where('v.id', '!=', $vehicleId)
            ->where('v.type', $type)
            ->where(function ($where) use ($brandId, $modelId, $bodyType, $value) {
                return $where->where('v.brand_id', $brandId)
                    ->orWhere('v.model_id', $modelId)
                    ->orWhere('v.bodytype_id', $bodyType)
                    ->orWhereBetween('v.value', [($value - 10000), ($value + 10000)]);
            })
            ->orderByRaw("
                v.bodytype_id = " . $brandId . " DESC,
                v.model_id = " . $modelId . " DESC,
                v.brand_id = " . $bodyType . " DESC,
                v.value BETWEEN '" . ($value - 10000) . "' AND '" . ($value + 10000) . "' DESC
            ")
            ->limit(4)
            ->get();

        // Gera a url do veiculo dinamicamente para fins de SEO
        // Gera a url completa para a imagem
        $result->transform(function ($vehicle) {
            $vehicle->url = HelpersSite::makeVehicleSeoURL($vehicle);
            $vehicle->image_url = HelpersSite::makeImageURL($vehicle);
            return $vehicle;
        });

        return $result;
    }

    /**
     * @param $data
     * @return JsonResponse
     * @throws CustomException
     */
    public function searchExternal($data): JsonResponse
    {
        try {
            $resources = $this->model->apply($data)->get();
            $resources = $this->makeHidenExternal($resources);
            return Helpers::reponse(true, $resources, 200, [], true);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * @param int $vehicle
     * @param int $publisher
     * @return JsonResponse
     * @throws CustomException
     */
    public function showExternal(int $vehicle, int $publisher): JsonResponse
    {
        try {
            $resources = $this->model->where('id', $vehicle)
                ->where('publisher_id', $publisher)
                ->with('images')
                ->first();

            if (empty($resources)) {
                return Helpers::reponse(false, [], 406, Config::get('errors.not_found'));
            }

            $resources = $this->makeHidenExternal($resources);

            return Helpers::reponse(true, $resources, 200, [], true);
        } catch (Exception $e) {
            DB::rollBack();
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, true, $e);
        }
    }

    /**
     * @param $resources
     * @return DefaultModelContracts|Vehicle|mixed
     */
    public function makeHidenExternal($resources)
    {
        $resources = $resources->makeHidden([
            'created_at',
            'updated_at',
            'deleted_at',
            'deleted_reason',
            'payment_status',
            'status',
            'disable_on',
            'publisher_id',
            'city',
            'brand',
            'model',
            'version',
            'transmission',
            'color',
            'bodyType',
            'fuel',
        ]);

        if ($resources instanceof $this->model) {
            foreach ($resources->accessories as &$accessory) {
                $accessory = $accessory->makeHidden([
                    'vehicle_type',
                    'key',
                    'deleted_at',
                    'created_at',
                    'updated_at',
                    'pivot_vehicle_id',
                    'pivot_accessory_id',
                    'pivot',
                ]);
            }

            foreach ($resources->images as &$image) {
                $image = $image->makeHidden([
                    'type',
                    'path',
                    'vehicle_id',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]);
            }
        } else {
            $resources = $resources->makeHidden(['accessories']);
        }

        return $resources;
    }
}
