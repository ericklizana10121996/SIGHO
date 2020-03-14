<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHojacosto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hojacosto', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('movimientoinicial_id');
            $table->integer('movimientofinal_id');
            $table->string('situacion',1);
            $table->integer('usuario_id')->unsigned()->nullable();
            $table->integer('hospitalizacion_id')->unsigned()->nullable();
            $table->foreign('usuario_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('hospitalizacion_id')->references('id')->on('hospitalizacion')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('hojacosto');
    }
}
