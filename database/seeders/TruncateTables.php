<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruncateTables extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        DB::table('states')->truncate();
        DB::table('cities')->truncate();
        DB::table('brands')->truncate();
        DB::table('models')->truncate();
        DB::table('versions')->truncate();
        DB::table('site_contacts')->truncate();
        DB::table('plans')->truncate();
        DB::table('publishers')->truncate();
        DB::table('vehicle_fields')->truncate();
        DB::table('vehicles_accessories')->truncate();
        DB::table('vehicles')->truncate();
    }
}
