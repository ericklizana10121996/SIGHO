<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableConceptopago extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conceptopago', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',500);
            $table->string('tipo',1);//I -> Ingreso, E -> Egreso
            $table->integer('monto');
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
        Schema::drop('conceptopago');
    }
}
