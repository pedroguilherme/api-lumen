<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Helpers\Helpers;
use App\Helpers\HelpersSite;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class HomeService
{
    private Vehicle $model;
    private BannerService $bannerService;

    public function __construct(Vehicle $model, BannerService $bannerService)
    {
        $this->model = $model;
        $this->bannerService = $bannerService;
    }

    public function getSite()
    {
        $result = [
            "banners" => $this->getBanners(),
            "highlights" => $this->getHighlights(),
            "recent" => $this->getRecent(),
        ];

        return Helpers::reponse(true, $result, 200, [], true);
    }

    private function getHighlights()
    {
        try {
            $return = DB::table('vehicles as v')
                ->select('v.id', 'v.type', 'v.plate', 'v.year_manufacture', 'v.year_model', 'v.mileage', 'v.doors',
                    'v.value', 'v.description', 'v.delivery', 'v.ipva_paid', 'v.warranty', 'v.armored', 'v.only_owner',
                    'v.seven_places', 'v.review', 'v.publisher_id', 'b.name     as brand', 'm.name     as model',
                    'vs.name    as version', 'vt.value   as transmission', 'vf.value   as fuel', 'vc.value   as color',
                    'vb.value   as bodytype', 'c.name     as city', 'c.state_id as uf',
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
                ->whereNull('v.deleted_at')
                ->orderByRaw("(v.spotlight = 'D') desc, (v.spotlight = 'G') desc, (v.spotlight = 'S') desc, (v.spotlight = 'N'), random()")
                ->limit(36)
                ->get();

            // Gera a url do veiculo dinamicamente para fins de SEO
            // Gera a url completa para a imagem
            $return->transform(function ($vehicle) {
                $vehicle->url = HelpersSite::makeVehicleSeoURL($vehicle);
                $vehicle->image_url = HelpersSite::makeImageURL($vehicle);
                return $vehicle;
            });

            return $return;
        } catch (\Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    private function getRecent()
    {
        try {
            $return = DB::table('vehicles as v')
                ->select('v.id', 'v.type', 'v.plate', 'v.year_manufacture', 'v.year_model', 'v.mileage', 'v.doors',
                    'v.value', 'v.description', 'v.delivery', 'v.ipva_paid', 'v.warranty', 'v.armored', 'v.only_owner',
                    'v.seven_places', 'v.review', 'v.publisher_id', 'b.name     as brand', 'm.name     as model',
                    'vs.name    as version', 'vt.value   as transmission', 'vf.value   as fuel', 'vc.value   as color',
                    'vb.value   as bodytype', 'c.name     as city', 'c.state_id as uf',
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
                ->whereNull('v.deleted_at')
                ->orderBy('v.created_at', 'DESC')
                ->limit(15)
                ->get();

            $return->transform(function ($vehicle) {
                $vehicle->url = HelpersSite::makeVehicleSeoURL($vehicle);
                $vehicle->image_url = HelpersSite::makeImageURL($vehicle);
                return $vehicle;
            });

            return $return;
        } catch (\Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

    private function getBanners()
    {
        try {
            return $this->bannerService->search([], false);
        } catch (\Exception $e) {
            $errors = $e instanceof CustomException ? $e->getErrorsArray() : Config::get('errors.internal');
            return Helpers::reponse(false, [], 500, $errors, false, $e);
        }
    }

}
