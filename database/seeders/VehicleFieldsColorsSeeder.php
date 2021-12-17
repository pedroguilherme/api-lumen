<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleFieldsColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $key = 'COLOR';
        $colors = [
            'Amarelo',
            'Azul',
            'Bege',
            'Branco',
            'Bronze',
            'Cinza',
            'Dourado',
            'Indefinida',
            'Laranja',
            'Marrom',
            'Prata',
            'Preto',
            'Rosa',
            'Roxo',
            'Verde',
            'Vermelho',
            'Vinho',
        ];

        foreach ($colors as $field) {
            DB::table('vehicle_fields')->insert(
                [
                    [
                        'vehicle_type' => 'C',
                        'key' => $key,
                        'value' => $field,
                        'created_at' => $now,
                        'updated_at' => $now
                    ],
                    [
                        'vehicle_type' => 'M',
                        'key' => $key,
                        'value' => $field,
                        'created_at' => $now,
                        'updated_at' => $now
                    ],
                    [
                        'vehicle_type' => 'T',
                        'key' => $key,
                        'value' => $field,
                        'created_at' => $now,
                        'updated_at' => $now
                    ],
                ]
            );
        }
    }
}
