<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDetallehojacosto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detallehojacosto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion',500);
            $table->decimal('cantidad',10,3);
            $table->decimal('precio',10,3);
            $table->integer('persona_id')->unsigned()->nullable();
            $table->integer('servicio_id')->unsigned()->nullable();
            $table->integer('hojacosto_id')->unsigned()->nullable();
            $table->foreign('persona_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('servicio_id')->references('id')->on('servicio')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('hojacosto_id')->references('id')->on('hojacosto')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('detallehojacosto');
    }
}
