<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCategoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categoria', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 100);
            $table->integer('nivel');
            $table->integer('categoria_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('categoria');
    }
}
