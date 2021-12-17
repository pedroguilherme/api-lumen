<?php

namespace Database\Factories;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $key = Arr::random(Config::get('constant.publishers_contacts'));
        $date = Carbon::now();
        return [
            'key' => $key,
            'value' => $key == 'contactEmail' || $key == 'offerEmail' ?
                $this->faker->safeEmail : $this->faker->numerify('(##) # ####-####'),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
