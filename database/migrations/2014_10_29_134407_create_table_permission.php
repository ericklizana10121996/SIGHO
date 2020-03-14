<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePermission extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('usertype_id')->unsigned();
			$table->integer('menuoption_id')->unsigned();
			$table->timestamps();
			$table->foreign('usertype_id')->references('id')->on('usertype')->onDelete('restrict')->onUpdate('restrict');
			$table->foreign('menuoption_id')->references('id')->on('menuoption')->onDelete('restrict')->onUpdate('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permission');
	}

}
