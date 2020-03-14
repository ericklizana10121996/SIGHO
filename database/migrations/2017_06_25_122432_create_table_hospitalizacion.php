<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHospitalizacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hospitalizacion', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->time('hora');
            $table->string('modo',200);
            $table->string('paquete',200);
            $table->date('fechaalta');
            $table->integer('usuario_id')->unsigned()->nullable();
            $table->integer('historia_id')->unsigned()->nullable();
            $table->integer('medico_id')->unsigned()->nullable();
            $table->integer('habitacion_id')->unsigned()->nullable();
            $table->integer('usuarioalta_id')->unsigned()->nullable();
            $table->foreign('historia_id')->references('id')->on('historia')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('medico_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('habitacion_id')->references('id')->on('habitacion')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('usuario_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('usuarioalta_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('hospitalizacion');
    }
}
