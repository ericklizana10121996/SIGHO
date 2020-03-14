<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDetallemovcaja extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detallemovcaja', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('movimiento_id')->unsigned()->nullable();
            $table->integer('servicio_id')->unsigned()->nullable();
            $table->integer('persona_id')->unsigned()->nullable();
            $table->string('descripcion',500);
            $table->decimal('cantidad',10,3);
            $table->decimal('precio',10,3);
            $table->decimal('pagodoctor',10,3);
            $table->decimal('pagohospital',10,3);
            $table->foreign('movimiento_id')->references('id')->on('movimiento')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('servicio_id')->references('id')->on('servicio')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('persona_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('detallemovcaja');
    }
}
