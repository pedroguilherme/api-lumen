<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SiteContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $contacts = [];
        $keys = Config::get('constant.site_contacts');
        $now = Carbon::now()->toDateTime();
        foreach ($keys as $key) {
            array_push($contacts, ['key' => $key, 'value' => '', 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('site_contacts')->insert($contacts);
    }
}
