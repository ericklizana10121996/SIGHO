<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCita extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cita', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->time('horainicio');
            $table->time('horafin');
            $table->integer('paciente_id')->unsigned()->nullable();
            $table->integer('historia_id')->unsigned()->nullable();
            $table->integer('doctor_id')->unsigned()->nullable();
            $table->string('situacion',1);//P => Pendiente / C => Confirmado Pagado
            $table->integer('movimiento_id')->unsigned()->nullable();
            $table->string('comentario',1000);
            $table->string('telefono',200);
            $table->string('paciente',200);
            $table->string('historia',20);
            $table->foreign('historia_id')->references('id')->on('historia')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('paciente_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('doctor_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('cita');
    }
}
