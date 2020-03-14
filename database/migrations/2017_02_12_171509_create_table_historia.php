<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHistoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historia', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->string('numero', 8);
            $table->integer('person_id')->unsigned()->nullable();
            $table->string('tipopaciente', 20);//CONVENIO / PARTICULAR / PACIENTE
            $table->integer('convenio_id')->unsigned()->nullable();
            $table->string('estadocivil', 100);
            $table->string('enviadopor', 200);
            $table->string('familiar', 200);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('person_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('convenio_id')->references('id')->on('convenio')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('historia');
    }
}
