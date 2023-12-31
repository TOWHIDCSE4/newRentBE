<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('session_users')) {
            Schema::create('session_users', function (Blueprint $table) {
                $table->id();
                $table->string('token');
                $table->string('refresh_token');
                $table->dateTime('token_expried');
                $table->dateTime('refresh_token_expried');
                $table->bigInteger('user_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_users');
    }
}
