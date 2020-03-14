<?php

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now          = new DateTime;
		$usertype_id = DB::table('usertype')->where('name', '=', 'ADMINISTRADOR PRINCIPAL')->first()->id;
		$list          = DB::table('menuoption')->get();
		foreach ($list as $key => $value) {
			DB::table('permission')->insert(array(
				array(
					'usertype_id' => $usertype_id,
					'menuoption_id'  => $value->id,
					'created_at'     => $now,
					'updated_at'     => $now
					)
				));
		}
		$usertype_id = DB::table('usertype')->where('name', '=', 'ADMINISTRADOR')->first()->id;
		$menuoption_id  = DB::table('menuoption')->where('name', '=', 'Clientes')->first()->id;
		DB::table('permission')->insert(array(
			array(
				'usertype_id' => $usertype_id,
				'menuoption_id'  => $menuoption_id,
				'created_at'     => $now,
				'updated_at'     => $now
				)
			));
		
		$menuoption_id = DB::table('menuoption')->where('name', '=', 'Usuario')->first()->id;
		DB::table('permission')->insert(array(
			array(
				'usertype_id' => $usertype_id,
				'menuoption_id'  => $menuoption_id,
				'created_at'     => $now,
				'updated_at'     => $now
				)
			));

		$usertype_id = DB::table('usertype')->where('name', '=', 'SECRETARIA')->first()->id;
		$list  = DB::table('menuoption')->where('menuoptioncategory_id', '=', 1)->get();
		foreach ($list as $key => $value) {
			DB::table('permission')->insert(array(
				array(
					'usertype_id' => $usertype_id,
					'menuoption_id'  => $value->id,
					'created_at'     => $now,
					'updated_at'     => $now
					)
				));
		}
    }
}
