<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRolpersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rolpersona', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rol_id')->unsigned()->nullable();
            $table->integer('person_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('rol_id')->references('id')->on('rol')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('person_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rolpersona');
    }
}
