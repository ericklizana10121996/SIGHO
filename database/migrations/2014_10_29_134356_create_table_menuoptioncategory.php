<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMenuoptioncategory extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menuoptioncategory', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 100);
			$table->integer('order');
			$table->string('icon', 60)->default('glyphicon glyphicon-expand');
			$table->integer('menuoptioncategory_id')->unsigned()->nullable();
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
		Schema::drop('menuoptioncategory');
	}

}
