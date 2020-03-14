<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDetalleanalisis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalleanalisis', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('analisis_id')->unsigned()->nullable();
            $table->integer('examen_id')->unsigned()->nullable();
            $table->string('descripcion',200);
            $table->string('detalle',200);
            $table->string('resultado',200);
            $table->foreign('analisis_id')->references('id')->on('analisis')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('examen_id')->references('id')->on('examen')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('detalleanalisis');
    }
}
