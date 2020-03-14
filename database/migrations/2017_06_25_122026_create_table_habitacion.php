<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHabitacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('habitacion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',100);
            $table->string('situacion',2);//O=>Ocupada//S=>Sucia//D=>Disponible
            $table->string('sexo',1);
            $table->integer('piso_id')->unsigned()->nullable();
            $table->integer('tipohabitacion_id')->unsigned()->nullable();
            $table->foreign('piso_id')->references('id')->on('piso')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('tipohabitacion_id')->references('id')->on('tipohabitacion')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('habitacion');
    }
}
