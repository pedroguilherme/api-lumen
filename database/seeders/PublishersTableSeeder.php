<?php

namespace Database\Seeders;

use App\Models\Publisher;
use App\Models\Contact;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class PublishersTableSeeder extends Seeder
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
                Publisher::factory()->count(30)->create()->each(function ($publisher) {
                    // Cria contatos para a loja 3 aleatorios
                    $publisher->contacts()->createMany(Contact::factory()->count(1)->make()->toArray());

                    // Cria usuário default da loja
                    $user = User::factory()->make([
                        'name' => $publisher->name,
                        'type' => ($publisher->type == 'J' ? 'S' : 'P')
                    ])->toArray();
                    $user['password'] = Hash::make('teste1234');
                    $publisher->user()->create($user);
                });
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
