<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('login')->unique(); // name
			$table->char('state', 1)->default('H');
			$table->integer('usertype_id')->unsigned();
			$table->integer('person_id')->unsigned();
			// ------ campos de laravel 5.1 -------
			$table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('issuperuser');
            $table->boolean('isstaff');
            $table->boolean('isactive');
            $table->timestamp('last_login')->nullable();
            $table->rememberToken();
            // ------------------------------------
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('usertype_id')->references('id')->on('usertype')->onDelete('restrict')->onUpdate('restrict');
			$table->foreign('person_id')->references('id')->on('person')->onDelete('restrict')->onUpdate('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user');
	}

}
