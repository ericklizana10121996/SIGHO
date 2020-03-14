<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableGuardia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guardia', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
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
        Schema::drop('guardia');
    }
}
