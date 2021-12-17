<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            TruncateTables::class, // Limpa todas as tabelas (TRUNCATE)
            UsersTableSeeder::class, // Usuário ADMIN
            StatesTableSeeder::class, // Estados verdadeiros
            CitiesTableSeeder::class, // Cidades verdadeiras
            BrandsTableSeeder::class, // Factory com 30
            ModelsTableSeeder::class, // Factory com 90
            VersionsTableSeeder::class, // Factory com 270
            SiteContactsTableSeeder::class, // Apenas para Iniciar os tipos default de contato
            PlansTableSeeder::class, // Apenas para Iniciar os tipos default de contato
            PublishersTableSeeder::class, // Pessoas
            VehicleFieldsFuelSeeder::class, // Campos Adicionais FUEL
            VehicleFieldsTransmissionSeeder::class, // Campos Adicionais TRANSMISSION
            VehicleFieldsBodyTypeSeeder::class, // Campos Adicionais BODYTYPE
            VehicleFieldsAcessoriesSeeder::class, // Campos Adicionais ACESSORIES
            VehicleFieldsColorsSeeder::class, // Campos Adicionais COLORS
            VehicleTableSeeder::class, // Factory com 100
        ]);
    }
}
