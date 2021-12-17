<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleFieldsFuelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $key = 'FUEL';
        $carFuel = [
            'Álcool',
            'Diesel',
            'Gasolina',
            'Flex',
            'Híbrido',
            'Elétrico',
            'GNV',
            'GNV e Flex',
            'GNV e Álcool',
            'GNV e Gasolina',
        ];
        $bikeFuel = [
            'Álcool',
            'Gasolina',
            'Outro',
        ];
        $truckFuel = [
            'Diesel',
            'Outro'
        ];

        foreach ($carFuel as $field) {
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
        foreach ($bikeFuel as $field) {
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
        foreach ($truckFuel as $field) {
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
