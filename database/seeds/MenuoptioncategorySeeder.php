<?php

use Illuminate\Database\Seeder;

class MenuoptioncategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = new DateTime;

		DB::table('menuoptioncategory')->insert(array(
				array(
					'name'     => 'Administración',
					'order'      => 1,
					'icon'      => 'fa fa-bank',
					'created_at' => $now,
					'updated_at' => $now
				),
				array(
					'name'     => 'Personas',
					'order'      => 2,
					'icon'      => 'fa fa-bank',
					'created_at' => $now,
					'updated_at' => $now
				),
				array(
					'name'     => 'Usuarios',
					'order'      => 3,
					'icon'      => 'fa fa-bank',
					'created_at' => $now,
					'updated_at' => $now
				)
			)
		);
    }
}
