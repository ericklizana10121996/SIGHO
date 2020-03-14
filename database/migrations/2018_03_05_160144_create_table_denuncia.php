<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDenuncia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('denuncia', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->integer('historia_id')->unsigned()->nullable();
            $table->string('seguro',200);
            $table->string('placa',200);
            $table->decimal('garantia', 10, 3);
            $table->string('denuncia',1);
            $table->integer('usuario_id')->unsigned()->nullable();
            $table->foreign('historia_id')->references('id')->on('historia')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('usuario_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::drop('denuncia');
    }
}
