<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHorario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horario', function (Blueprint $table) {
            $table->increments('id');
            $table->date('desde');
            $table->date('hasta');
            $table->text('observaciones');
            $table->text('horarios');
            $table->integer('person_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::drop('horario');
    }
}
