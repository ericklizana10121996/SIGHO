<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableExamen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examen', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion',200);
            $table->integer('tipoexamen_id')->unsigned()->nullable();
            $table->foreign('tipoexamen_id')->references('id')->on('tipoexamen')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('examen');
    }
}
