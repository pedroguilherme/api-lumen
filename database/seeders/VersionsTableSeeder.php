<?php

namespace Database\Seeders;

use App\Models\Version;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class VersionsTableSeeder extends Seeder
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
                Version::factory()->count(270)->create();
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
}
