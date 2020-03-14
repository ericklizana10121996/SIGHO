<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePerson extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('person', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('lastname',100)->nullable();
			$table->string('firstname',100)->nullable();
			$table->string('bussinesname',100)->nullable();
			$table->string('nombres',100)->nullable();
			$table->string('apellidopaterno',100)->nullable();
			$table->string('apellidomaterno',100)->nullable();
			$table->char('dni',8)->nullable();
			$table->char('ruc',11)->nullable();
			$table->string('cmp',10)->nullable();
			$table->string('direccion',120)->nullable();
			$table->string('telefono',15)->nullable();
			$table->string('celular',15)->nullable();
			$table->string('email',30)->nullable();
			$table->char('tipo',1)->nullable(); // N= Natural , J = Juridica
			$table->integer('workertype_id')->unsigned()->nullable();
			$table->integer('especialidad_id')->unsigned()->nullable();
            $table->date('fechanacimiento')->unsigned()->nullable();
            $table->char('tipomedico',1)->nullable(); // E = Especialista , G = General
            $table->string('rne',10)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->foreign('workertype_id')->references('id')->on('workertype')->onUpdate('restrict')->onDelete('restrict');
			$table->foreign('especialidad_id')->references('id')->on('especialidad')->onUpdate('restrict')->onDelete('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('person');
	}

}
