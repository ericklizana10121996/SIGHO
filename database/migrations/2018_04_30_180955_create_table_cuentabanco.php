<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCuentabanco extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuentabanco', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion',200);
            $table->string('moneda',10);
            $table->integer('banco_id')->unsigned()->nullable();
            $table->foreign('banco_id')->references('id')->on('banco')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('cuentabanco');
    }
}
