<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTipodocumento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipodocumento', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',100);
            $table->string('abreviatura',10);
            $table->string('stock',1);
            $table->string('codigo',10);
            $table->integer('tipomovimiento_id')->unsigned()->nullable();
            $table->foreign('tipomovimiento_id')->references('id')->on('tipomovimiento')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('tipodocumento');
    }
}
