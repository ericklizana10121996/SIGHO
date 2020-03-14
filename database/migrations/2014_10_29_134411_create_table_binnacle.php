<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBinnacle extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('binnacle', function(Blueprint $table)
		{
			$table->increments('id');
			$table->char('action', 1); // C->create, R->Read, U->Update, D->Delete
			$table->timestamp('date');
			$table->string('ip', 15);
			$table->integer('user_id')->unsigned();
			$table->integer('recordid')->unsigned();
			$table->string('table', 50);
			$table->text('detail');
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('user_id')->references('id')->on('user')->onDelete('restrict')->onUpdate('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('binnacle');
	}

}
