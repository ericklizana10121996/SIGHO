<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNumeracion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('numeracion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('serie');
            $table->integer('numero');
            $table->integer('tipomovimiento_id')->unsigned()->nullable();
            $table->integer('tipodocumento_id')->unsigned()->nullable();
            $table->foreign('tipomovimiento_id')->references('id')->on('tipomovimiento')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('tipodocumento_id')->references('id')->on('tipodocumento')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('numeracion');
    }
}
