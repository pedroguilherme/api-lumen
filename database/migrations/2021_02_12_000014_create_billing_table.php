<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference', 7);
            $table->string('status')->nullable()->default('waiting_payment');
            $table->string('description')->nullable();
            $table->string('payment_method')->nullable();

            $table->dateTime('expiration')->nullable();
            $table->dateTime('payed_at')->nullable();
            $table->decimal('value');
            $table->string('cred_card_info', 4)->nullable();

            $table->string('external_transaction_id')->nullable();
            $table->string('boleto_url')->nullable();
            $table->string('boleto_barcode')->nullable();

            $table->string('plan_pf')->nullable();
            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('publisher_id');
            $table->bigInteger('vehicle_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('publisher_id')->references('id')->on('publishers');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billings');
    }
}
