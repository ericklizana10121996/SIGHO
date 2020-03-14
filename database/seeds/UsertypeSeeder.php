<?php

use Illuminate\Database\Seeder;

class UsertypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = new DateTime;

		DB::table('usertype')->insert(array(
				array(
					'name' => 'ADMINISTRADOR PRINCIPAL',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
		DB::table('usertype')->insert(array(
				array(
					'name' => 'ADMINISTRADOR',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
		DB::table('usertype')->insert(array(
				array(
					'name' => 'SECRETARIA',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
		DB::table('usertype')->insert(array(
				array(
					'name' => 'OPERARIO',
					'created_at' => $now,
					'updated_at' => $now
				)
			));
    }
}
