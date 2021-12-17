<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleFieldsBodyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $key = 'BODY_TYPE';
        $carBodyType = [
            'Buggy',
            'Conversível',
            'Cupê',
            'Hatchback',
            'Minivan',
            'Perua/SW',
            'Picape',
            'Sedã',
            'Utilitário esportivo',
            'Van/Utilitário',
        ];
        $bikeBodyTypes = [
            "Custom",
            "Elétrica",
            "Esportiva",
            "Naked",
            "Off road",
            "Quadriciclo",
            "Scooter",
            "Street",
            "Supermotard",
            "Touring",
            "Trail",
            "Trial",
            "Triciclo",
            "Utilitária",
        ];
        $truckBodyTypes = [];

        foreach ($carBodyType as $field) {
            DB::table('vehicle_fields')->insert(
                [
                    'vehicle_type' => 'C',
                    'key' => $key,
                    'value' => $field,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
        foreach ($bikeBodyTypes as $field) {
            DB::table('vehicle_fields')->insert(
                [
                    'vehicle_type' => 'M',
                    'key' => $key,
                    'value' => $field,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
        foreach ($truckBodyTypes as $field) {
            DB::table('vehicle_fields')->insert(
                [
                    'vehicle_type' => 'T',
                    'key' => $key,
                    'value' => $field,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
    }
}
