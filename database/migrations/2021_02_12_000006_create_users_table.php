<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->char('type', 1);

            $table->string('email')->unique();
            $table->string('name');
            $table->string('password');

            $table->timestamp('email_verified_at')->default(NULL)->nullable();

            $table->bigInteger('publisher_id', false, true)->nullable()->default(NULL);

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('users');
    }
}
