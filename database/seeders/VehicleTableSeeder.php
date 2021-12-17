<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class VehicleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            if (Config::get('app.env') != 'production') {
                Vehicle::factory()->count(100)->create()->each(function ($vehicle) {
                    $acessories = VehicleField::query()
                        ->where('vehicle_type', $vehicle->type)
                        ->where('key', 'ACCESSORY')
                        ->get();
                    foreach ($acessories as $acessory) {
                        $vehicle->accessories()->attach($acessory);
                    }
                });
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
