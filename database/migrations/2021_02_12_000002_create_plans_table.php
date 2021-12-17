<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');

            $table->string('name');
            $table->string('description');

            $table->integer('normal');
            $table->integer('silver');
            $table->integer('gold');
            $table->integer('diamond');
            $table->integer('recurrence');

            $table->decimal('fantasy_value')->nullable()->default(NULL);
            $table->decimal('value');

            $table->string('external_plan_id')->nullable()->default(NULL);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
}
