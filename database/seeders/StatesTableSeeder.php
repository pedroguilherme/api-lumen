<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        DB::table('states')->insert([
            ['uf' => 'AC', 'name' => 'Acre', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'AL', 'name' => 'Alagoas', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'AP', 'name' => 'Amapá', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'AM', 'name' => 'Amazonas', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'BA', 'name' => 'Bahia', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'CE', 'name' => 'Ceará', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'DF', 'name' => 'Distrito Federal', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'ES', 'name' => 'Espírito Santo', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'GO', 'name' => 'Goiás', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'MA', 'name' => 'Maranhão', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'MT', 'name' => 'Mato Grosso', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'MS', 'name' => 'Mato Grosso do Sul', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'MG', 'name' => 'Minas Gerais', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'PA', 'name' => 'Pará', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'PB', 'name' => 'Paraíba', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'PR', 'name' => 'Paraná', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'PE', 'name' => 'Pernambuco', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'PI', 'name' => 'Piauí', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'RJ', 'name' => 'Rio de Janeiro', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'RN', 'name' => 'Rio Grande do Norte', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'RS', 'name' => 'Rio Grande do Sul', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'RO', 'name' => 'Rondônia', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'RR', 'name' => 'Roraima', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'SC', 'name' => 'Santa Catarina', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'SP', 'name' => 'São Paulo', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'SE', 'name' => 'Sergipe', 'created_at' => $now, 'updated_at' => $now],
            ['uf' => 'TO', 'name' => 'Tocantins', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
