<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('type', 1);
            $table->string('plate');
            $table->string('year_manufacture', 4);
            $table->string('year_model', 4);
            $table->integer('mileage');
            $table->integer('doors');
            $table->decimal('value');
            $table->text('description');

            $table->boolean('delivery')->default(false);
            $table->boolean('ipva_paid')->default(false);
            $table->boolean('warranty')->default(false);
            $table->boolean('armored')->default(false);
            $table->boolean('only_owner')->default(false);
            $table->boolean('seven_places')->default(false);
            $table->boolean('review')->default(false);

            $table->char('spotlight', 1)->nullable();
            $table->string('payment_status', 20)->nullable();
            $table->string('status', 30)->nullable();
            $table->date('disable_on')->nullable();

            $table->bigInteger('publisher_id');
            $table->bigInteger('city_id');
            $table->bigInteger('fuel_id');
            $table->bigInteger('transmission_id');
            $table->bigInteger('color_id');
            $table->bigInteger('bodytype_id');

            $table->bigInteger('brand_id');
            $table->bigInteger('model_id');
            $table->bigInteger('version_id');

            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_reason')->nullable();

            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('fuel_id')->references('id')->on('vehicle_fields');
            $table->foreign('transmission_id')->references('id')->on('vehicle_fields');
            $table->foreign('color_id')->references('id')->on('vehicle_fields');
            $table->foreign('bodytype_id')->references('id')->on('vehicle_fields');

            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('model_id')->references('id')->on('models');
            $table->foreign('version_id')->references('id')->on('versions');

            $table->foreign('publisher_id')->references('id')->on('publishers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
