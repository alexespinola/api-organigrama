<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAreasRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('users_areas_roles', function (Blueprint $table) {
        $table->unsignedBigInteger('id_user');
        $table->foreign('id_user')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

        $table->unsignedBigInteger('id_rol');
        $table->foreign('id_rol')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');

        $table->integer('id_area');
        $table->integer('id_parent');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('users_areas_roles');
    }
}
