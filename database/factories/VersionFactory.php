<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\Version;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class VersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Version::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::now();
        return [
            'model_id' => Model::all()->random()->id,
            'name' => $this->faker->unique()->company,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
