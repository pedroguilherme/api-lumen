<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::now();
        return [
            'brand_id' => Brand::all()->random()->id,
            'name' => $this->faker->unique()->company,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
