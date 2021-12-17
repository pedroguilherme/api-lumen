<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesAccessoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles_accessories', function (Blueprint $table) {

            $table->bigInteger('vehicle_id');
            $table->bigInteger('accessory_id');

            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->foreign('accessory_id')->references('id')->on('vehicle_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles_accessories');
    }
}
