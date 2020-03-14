<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre',100);
            $table->string('aseguradora',100);
            $table->string('ruc',11);
            $table->string('razonsocial',200);
            $table->string('direccion',200);
            $table->decimal('deducible',10,2);
            $table->decimal('coaseguro',10,2);
            $table->decimal('consulta',10,2);
            $table->string('tipopago',20);
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
        Schema::drop('plan');
    }
}
