<?php

namespace App\Services;

use App\Helpers\Helpers;
use App\Helpers\HelpersSite;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchService
{
    private Vehicle $model;
    private BannerService $bannerService;
    private string $s3Url;
    private string $siteUrl;

    public function __construct(Vehicle $model, BannerService $bannerService)
    {
        $this->model = $model;
        $this->bannerService = $bannerService;
        $this->s3Url = Env::get('AWS_URL');
        $this->siteUrl = config('app.site_url');
    }

    public function getSite(Collection $data = null)
    {
        $result = DB::table('vehicles as v')
            ->select('v.id', 'v.type', 'v.plate', 'v.year_manufacture', 'v.year_model', 'v.mileage', 'v.doors',
                'v.value', 'v.description', 'v.delivery', 'v.ipva_paid', 'v.warranty', 'v.armored', 'v.only_owner',
                'v.seven_places', 'v.review', 'v.publisher_id', 'b.name as brand', 'v.brand_id', 'm.name as model',
                'v.model_id',
                'vs.name as version', 'v.version_id', 'vt.value as transmission', 'vf.value as fuel',
                'vc.value as color',
                'vb.value as bodytype', 'c.name as city', 'c.state_id as uf',
                DB::raw('(select f.path from files as f WHERE "v"."id" = "f"."vehicle_id" order by f."order" LIMIT 1) as image'))
            ->join('publishers as p', 'v.publisher_id', '=', 'p.id')
            ->join('brands as b', 'b.id', '=', 'v.brand_id')
            ->join('models as m', 'm.id', '=', 'v.model_id')
            ->join('versions as vs', 'vs.id', '=', 'v.version_id')
            ->join('vehicle_fields as vt', 'v.transmission_id', '=', 'vt.id')
            ->join('vehicle_fields as vf', 'v.fuel_id', '=', 'vf.id')
            ->join('vehicle_fields as vc', 'v.color_id', '=', 'vc.id')
            ->join('vehicle_fields as vb', 'v.bodytype_id', '=', 'vb.id')
            ->join('cities as c', 'v.city_id', '=', 'c.id')
            ->where(function ($query) {
                $query->orWhereIn('p.payment_situation', ['paid', 'free'])
                    ->orWhere('v.payment_status', 'paid');
            })
            ->whereNull('v.deleted_at');

        foreach ($data as $column => $values) {
            switch ($column) {
                case 'type':
                case 'brand_id':
                case 'model_id':
                case 'city_id':
                case 'fuel_id':
                case 'transmission_id':
                case 'bodytype_id':
                case 'color_id':
                case 'publisher_id':
                    // Pesquisa de Veículos POR FK`s, pode ter mais de uma
                    $result->where(function ($where) use ($column, $values) {
                        foreach ($values as $value) {
                            if (!empty($value)) {
                                if ($column == 'type') {
                                    $where->orWhere('v.' . $column, $value);
                                } else {
                                    $value = intval($value);
                                    if ($value > 0) {
                                        $where->orWhere('v.' . $column, $value);
                                    }
                                }
                            }
                        }
                    });
                    break;
                case 'value':
                case 'mileage':
                case 'year_model':
                    // Pesquisa de Veículos POR Range
                    if (isset($values["de"]) && !empty($values["de"])) {
                        $result->where('v.' . $column, '>=', $values["de"]);
                    }
                    if (isset($values["ate"]) && !empty($values["ate"])) {
                        $result->where('v.' . $column, '<=', $values["ate"]);
                    }
                    break;
                case 'delivery':
                case 'ipva_paid':
                case 'only_owner':
                case 'warranty':
                case 'armored':
                case 'seven_places':
                case 'review':
                    // Pesquisa de Veículo POR Atributos
                    if (is_bool($values)) {
                        $result->where('v.' . $column, '=', $values);
                    }
                    break;
            }
        }

        if (isset($data['order']) && !empty($data['order'])) {
            $order = explode('|', $data['order']);
            if (count($order) == 2) {
                switch ($order[0]) {
                    case 'id':
                        $result->orderBy('v.created_at', ($order[1] == 'asc' ? 'asc' : 'desc'));
                        break;
                    case 'value':
                        $result->orderBy('v.value', ($order[1] == 'asc' ? 'asc' : 'desc'));
                        break;
                    case 'year_model':
                        $result->orderBy('v.year_model', ($order[1] == 'asc' ? 'asc' : 'desc'));
                        break;
                }
            }
        }

        $result->orderByRaw("(v.spotlight = 'D') desc, (v.spotlight = 'G') desc, (v.spotlight = 'S') desc, (v.spotlight = 'N'), random()");

        if (isset($data['all']) && $data['all'] == true) {
            $result = $result->get()->transform(function ($vehicle) {
                $vehicle->url = HelpersSite::makeVehicleSeoURL($vehicle);
                $vehicle->image_url = HelpersSite::makeImageURL($vehicle);
                return $vehicle;
            });
        } else {
            $result = $result->paginate(30);
            $result->getCollection()->transform(function ($vehicle) {
                $vehicle->url = HelpersSite::makeVehicleSeoURL($vehicle);
                $vehicle->image_url = HelpersSite::makeImageURL($vehicle);
                return $vehicle;
            });
        }


        return Helpers::reponse(true, $result, 200, [], true);
    }

}
