<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMensajefacturacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mensajefacturacion', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->time('hora');
            $table->string('mensaje',1000);
            $table->integer('usuario_id')->unsigned()->nullable();
            $table->integer('historia_id')->unsigned()->nullable();
            $table->foreign('usuario_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('historia_id')->references('id')->on('historia')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('mensajefacturacion');
    }
}
