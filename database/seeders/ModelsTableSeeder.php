<?php

namespace Database\Seeders;

use App\Models\Model;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class ModelsTableSeeder extends Seeder
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
                Model::factory()->count(90)->create();
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
