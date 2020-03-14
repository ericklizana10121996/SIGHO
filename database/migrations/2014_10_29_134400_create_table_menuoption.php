<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMenuoption extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menuoption', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 60);
			$table->string('link', 120);
			$table->integer('order');
			$table->string('descripcion', 50)->nullable();
			$table->string('icon', 60)->default('glyphicon glyphicon-expand');
			$table->integer('menuoptioncategory_id')->unsigned();
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('menuoptioncategory_id')->references('id')->on('menuoptioncategory')->onDelete('restrict')->onUpdate('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('menuoption');
	}

}
