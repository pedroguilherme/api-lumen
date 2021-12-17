<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\City;
use App\Models\Plan;
use App\Models\Publisher;
use App\Models\Vehicle;
use App\Models\VehicleField;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = Arr::random(Arr::except(Config::get('constant.vehicle_type'), ['Caminhão']));
        $spotlight = Arr::random(array_keys(Config::get('constant.spotlight_abbreviation')));
        $date = Carbon::now();
        while (true) {
            try {
                $brand = Brand::query()->where('vehicle_type', $type)->get()->random();
                $model = $brand->models->random();
                $version = $model->versions->random();
                break;
            } catch (\Exception $e) {
                continue;
            }
        }

        $fuel = VehicleField::query()->where('vehicle_type', $type)->where('key', 'FUEL')->get()->random();
        $transmission = VehicleField::query()->where('vehicle_type', $type)->where('key',
            'TRANSMISSION')->get()->random();
        $color = VehicleField::query()->where('vehicle_type', $type)->where('key', 'COLOR')->get()->random();
        $bodyType = VehicleField::query()->where('vehicle_type', $type)->where('key', 'BODY_TYPE')->get()->random();

        return [
            'type' => $type,
            'plate' => $this->faker->numerify('AAA-####'),
            'year_manufacture' => $this->faker->year,
            'year_model' => $this->faker->year,
            'mileage' => intval($this->faker->randomFloat(null, 10, 99999)),
            'doors' => Arr::random([2, 4]),
            'value' => $this->faker->randomFloat(2, 10000, 150000),
            'description' => $this->faker->text(),
            'delivery' => Arr::random([true, false]),
            'ipva_paid' => Arr::random([true, false]),
            'warranty' => Arr::random([true, false]),
            'armored' => Arr::random([true, false]),
            'only_owner' => Arr::random([true, false]),
            'seven_places' => Arr::random([true, false]),
            'review' => Arr::random([true, false]),
            'spotlight' => $spotlight,
            'payment_status' => null,
            'status' => null,
            'disable_on' => null,
            'publisher_id' => Publisher::all()->random()->id,
            'city_id' => City::all()->random()->id,
            'fuel_id' => $fuel->id,
            'transmission_id' => $transmission->id,
            'color_id' => $color->id,
            'bodytype_id' => $bodyType->id,
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'version_id' => $version->id,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
