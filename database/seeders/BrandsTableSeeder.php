<?php

namespace Database\Seeders;

use App\Models\Brand;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class BrandsTableSeeder extends Seeder
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
                Brand::factory()->count(30)->create();
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
