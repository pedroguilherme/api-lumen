<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('type', 1)->default('O');

            $table->string('client_name');
            $table->string('client_contact');
            $table->string('client_email');
            $table->string('client_init_message');

            $table->bigInteger('client_car_version_id', false, true)->nullable()->default(NULL);
            $table->string('client_car_year_manufacture', 4)->nullable()->default(NULL);
            $table->string('client_car_year_model', 4)->nullable()->default(NULL);
            $table->string('client_car_details')->nullable()->default(NULL);
            $table->string('client_car_discount')->nullable()->default(NULL);

            $table->boolean('email_sended')->default('false');

            $table->bigInteger('publisher_id');
            $table->bigInteger('vehicle_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('publisher_id')->references('id')->on('publishers');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->foreign('client_car_version_id')->references('id')->on('versions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
}
