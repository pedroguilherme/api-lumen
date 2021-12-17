<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublishersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->char('type', 1);

            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('cpf_cnpj')->nullable();
            $table->string('cep')->nullable();
            $table->string('number')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('address')->nullable();
            $table->string('complement')->nullable();

            $table->string('description')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_situation')->default('first');
            $table->date('payment_nextcheck')->nullable();
            $table->date('paid_in')->nullable();
            $table->string('access_status')->nullable()->default('limited');
            $table->string('api_token')->nullable();
            $table->string('logo')->nullable();
            $table->text('work_schedule')->nullable();

            $table->string('external_subscription_id')->nullable();

            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('future_plan_id')->nullable();
            $table->bigInteger('city_id');
            $table->bigInteger('free')->nullable();
            $table->date('free_active_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->text('deleted_reason')->nullable();

            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('future_plan_id')->references('id')->on('plans');
            $table->foreign('city_id')->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('publishers');
    }
}
