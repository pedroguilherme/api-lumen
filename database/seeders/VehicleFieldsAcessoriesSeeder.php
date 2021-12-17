<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleFieldsAcessoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $key = 'ACCESSORY';
        $carAcessories = [
            'Airbag',
            'Alarme',
            'Ar condicionado',
            'Ar quente',
            'Banco com regulagem de altura',
            'Bancos dianteiros com aquecimento',
            'Bancos em couro',
            'Capota marítima',
            'CD e MP3 Player',
            'CD Player',
            'Computador de bordo',
            'Controle automático de velocidade',
            'Controle de tração',
            'Desembaçador traseiro',
            'Direção hidráulica',
            'Disqueteira',
            'DVD Player',
            'Encosto de cabeça traseiro',
            'Farol de xenônio',
            'Freio ABS',
            'GPS',
            'Limpador traseiro',
            'Protetor de caçamba',
            'Rádio',
            'Rádio e toca fitas',
            'Retrovisor fotocrômico',
            'Retrovisores elétricos',
            'Rodas de liga leve',
            'Sensor de chuva',
            'Sensor de estacionamento',
            'Teto solar',
            'Tração 4x4',
            'Travas elétricas',
            'Vidros elétricos',
            'Volante com regulagem de altura'
        ];
        $bikeAcessories = [
            'ABS',
            'Alarme',
            'Amortecedor de direção',
            'Bolsa/Baú/Bauleto',
            'Computador de bordo',
            'Contra peso no guidon',
            'Escapamento esportivo',
            'Faróis de neblina',
            'GPS',
            'SOM',
        ];
        $truckAcessories = [
            'GPS',
        ];

        foreach ($carAcessories as $field) {
            DB::table('vehicle_fields')->insert([
                'vehicle_type' => 'C',
                'key' => $key,
                'value' => $field,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        foreach ($bikeAcessories as $field) {
            DB::table('vehicle_fields')->insert([
                'vehicle_type' => 'M',
                'key' => $key,
                'value' => $field,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        foreach ($truckAcessories as $field) {
            DB::table('vehicle_fields')->insert([
                'vehicle_type' => 'T',
                'key' => $key,
                'value' => $field,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
    }
}
