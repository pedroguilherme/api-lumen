<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        DB::table('users')->insert([
            'type' => Config::get('constant.user_type.admin'),
            'email' => 'admin@empresa.com.br',
            'name' => 'Admin',
            'password' => Hash::make('123456'),
            'publisher_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
