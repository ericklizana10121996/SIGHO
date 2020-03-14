<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDetalleexamen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalleexamen', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion',200);
            $table->string('detalle',200);
            $table->integer('examen_id')->unsigned()->nullable();
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
        Schema::drop('detalleexamen');
    }
}
