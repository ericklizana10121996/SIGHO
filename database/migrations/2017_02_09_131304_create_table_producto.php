<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProducto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigobarra', 100);
            $table->string('nombre', 100);
            $table->integer('fraccion');
            $table->decimal('preciocompra',9,2);
            $table->decimal('precioventa',9,2);
            $table->char('tipo', 1); // P = Producto, I = Insumo, O = Otros 
            $table->char('afecto', 2); // SI , NO
            $table->decimal('stockseguridad',9,2);
            $table->integer('distribuidora_id')->unsigned()->nullable();
            $table->integer('categoria_id')->unsigned()->nullable();
            $table->integer('laboratorio_id')->unsigned()->nullable();
            $table->integer('unidad_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('distribuidora_id')->references('id')->on('distribuidora')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('laboratorio_id')->references('id')->on('laboratorio')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('unidad_id')->references('id')->on('unidad')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('producto');
    }
}
