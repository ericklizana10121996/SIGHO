<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableServicio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicio', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',100);
            $table->decimal('precio',9,2);
            $table->decimal('pagohospital',9,2);
            $table->decimal('pagodoctor',9,2);
            $table->integer('tiposervicio_id')->unsigned()->nullable();
            $table->string('tipopago',20);
            $table->foreign('tiposervicio_id')->references('id')->on('tiposervicio')->onDelete('restrict')->onUpdate('restrict');

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
        Schema::drop('servicio');
    }
}
