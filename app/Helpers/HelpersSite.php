<?php

namespace App\Helpers;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Storage;

class HelpersSite
{
    /**
     * @param $vehicle
     * @return string
     */
    public static function makeVehicleSeoURL($vehicle)
    {
        $seo = '';
        $seo .= ($vehicle->type == 'C' ? 'carros' : 'outros') . '/';
        $seo .= ($vehicle->type == 'C' ? 'usados-seminovos' : 'usadas-seminovas') . '-zerokm/';
        $seo .= (Helpers::sanitizeString(strtolower($vehicle->uf), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($vehicle->city), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($vehicle->brand), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($vehicle->model), '-')) . '/';
        $seo .= (Helpers::sanitizeString(strtolower($vehicle->version),
                '-')) . '-' . $vehicle->year_model . '/';

        return "/anuncio/" . $seo . $vehicle->id;
    }

    /**
     * @param $vehicle
     * @return string
     */
    public static function makeImageURL($vehicle)
    {
        return Storage::disk('s3')->url($vehicle->image);
    }
}

