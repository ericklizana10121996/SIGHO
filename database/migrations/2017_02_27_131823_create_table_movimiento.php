<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMovimiento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('serie')->unsigned()->nullable();
            $table->integer('numero')->unsigned()->nullable();
            $table->date('fecha');
            $table->integer('persona_id')->unsigned()->nullable();
            $table->integer('responsable_id')->unsigned()->nullable();
            $table->integer('conceptopago_id')->unsigned()->nullable();
            $table->integer('tipomovimiento_id')->unsigned()->nullable();
            $table->integer('tipodocumento_id')->unsigned()->nullable();
            $table->decimal('subtotal',10,3);
            $table->decimal('igv',10,3);
            $table->decimal('total',10,3);
            $table->string('comentario',500);
            $table->integer('almacen_id')->unsigned()->nullable();
            $table->string('voucher',20);
            $table->decimal('totalpagado',10,3);
            $table->string('tarjeta',20);
            $table->integer('movimiento_id')->unsigned()->nullable();
            $table->integer('caja_id')->unsigned()->nullable();
            $table->foreign('persona_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('responsable_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('conceptopago_id')->references('id')->on('conceptopago')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('tipomovimiento_id')->references('id')->on('tipomovimiento')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('tipodocumento_id')->references('id')->on('tipodocumento')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('almacen_id')->references('id')->on('almacen')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('caja_id')->references('id')->on('caja')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('movimiento_id')->references('id')->on('movimiento')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('movimiento');
    }
}
