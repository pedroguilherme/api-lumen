<?php

namespace Database\Factories;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::now();
        return [
            'vehicle_type' => Arr::random(Config::get('constant.vehicle_type')),
            'name' => $this->faker->unique()->company,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
