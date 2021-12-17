<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        DB::table('plans')->insert([
            // BÁSICOS
            [
                'type' => Config::get('constant.plans.basic'),
                'name' => 'Plano Básico',
                'description' => 'Plano Básico',
                'normal' => 10,
                'silver' => 5,
                'gold' => 3,
                'diamond' => 2,
                'recurrence' => 1,
                'fantasy_value' => null,
                'value' => 99.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => Config::get('constant.plans.basic'),
                'name' => 'Plano Básico 6 meses',
                'description' => 'Plano Básico 6 meses',
                'normal' => 10,
                'silver' => 5,
                'gold' => 3,
                'diamond' => 2,
                'recurrence' => 6,
                'fantasy_value' => 594.00,
                'value' => 475.20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // INTERMEDIÁRIOS
            [
                'type' => Config::get('constant.plans.intermediary'),
                'name' => 'Plano Intermediário',
                'description' => 'Plano Intermediário',
                'normal' => 9999,
                'silver' => 14,
                'gold' => 9,
                'diamond' => 7,
                'recurrence' => 1,
                'fantasy_value' => null,
                'value' => 149.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => Config::get('constant.plans.intermediary'),
                'name' => 'Plano Intermediário 6 meses',
                'description' => 'Plano Intermediário 6 meses',
                'normal' => 9999,
                'silver' => 14,
                'gold' => 9,
                'diamond' => 7,
                'recurrence' => 6,
                'fantasy_value' => 894.00,
                'value' => 715.20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // AVANÇADOS
            [
                'type' => Config::get('constant.plans.advanced'),
                'name' => 'Plano Avançado',
                'description' => 'Plano Avançado',
                'normal' => 9999,
                'silver' => 30,
                'gold' => 20,
                'diamond' => 15,
                'recurrence' => 1,
                'fantasy_value' => null,
                'value' => 199.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => Config::get('constant.plans.advanced'),
                'name' => 'Plano Avançado 6 meses',
                'description' => 'Plano Avançado 6 meses',
                'normal' => 9999,
                'silver' => 30,
                'gold' => 20,
                'diamond' => 15,
                'recurrence' => 6,
                'fantasy_value' => 1194.00,
                'value' => 955.20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
