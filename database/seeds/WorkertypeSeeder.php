<?php

use Illuminate\Database\Seeder;

class WorkertypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = new DateTime;

		DB::table('workertype')->insert(array(
				array(
					'name' => 'ADMINISTRADOR PRINCIPAL',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
		DB::table('workertype')->insert(array(
				array(
					'name' => 'ADMINISTRADOR',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
		DB::table('workertype')->insert(array(
				array(
					'name' => 'SECRETARIA',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
		DB::table('workertype')->insert(array(
				array(
					'name' => 'OPERARIO',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
    }
}
