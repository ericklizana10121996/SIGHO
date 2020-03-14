<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDistrito extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('distrito', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('nombre', 100);
			$table->integer('provincia_id')->unsigned();
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('provincia_id')->references('id')->on('provincia')->onUpdate('restrict')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('distrito');
	}

}
