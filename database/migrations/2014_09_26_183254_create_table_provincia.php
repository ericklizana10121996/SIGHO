<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProvincia extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('provincia', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('nombre', 30);
			$table->integer('departamento_id')->unsigned();
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('departamento_id')->references('id')->on('departamento')->onUpdate('restrict')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('provincia');
	}

}
