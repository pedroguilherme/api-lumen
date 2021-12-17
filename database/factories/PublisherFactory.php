<?php

namespace Database\Factories;

use App\Models\Publisher;
use App\Models\City;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class PublisherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Publisher::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = Arr::random(Config::get('constant.people_type'));
        $date = Carbon::now();
        return [
            'type' => $type,
            'name' => $type == 'F' ? $this->faker->unique()->name : $this->faker->unique()->company,
            'company_name' => $type == 'F' ? null : $this->faker->unique()->company,
            'cpf_cnpj' => $type == 'F' ? $this->faker->numerify('###.###.###-##') : $this->faker->numerify('##.###.###/0001-##'),
            'cep' => $this->faker->numerify('#####-###'),
            'number' => $this->faker->randomNumber(3),
            'neighborhood' => $this->faker->streetSuffix,
            'address' => $this->faker->streetName,
            'complement' => $this->faker->secondaryAddress,
            'description' => $this->faker->text(),
            'payment_method' => null,
            'payment_situation' => 'first',
            'api_token' => hash('sha256', $this->faker->randomNumber(3)),
            'logo' => null,
            'work_schedule' => $this->faker->text(),
            'plan_id' => Plan::all()->random()->id,
            'city_id' => City::all()->random()->id,
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
