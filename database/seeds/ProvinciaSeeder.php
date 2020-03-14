<?php

use Illuminate\Database\Seeder;

class ProvinciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ahora      = new DateTime; 
		$provincias = DB::connection('pgsql')->table('tb_provincia')->get();
		foreach ($provincias as $key => $value) {
			DB::table('provincia')->insert(array(
					'id'              => $value->tb_provincia_id,
					'nombre'          => $value->tb_provincia_nombre,
					'departamento_id' => $value->tb_departamento_id,
					'created_at'       => $ahora,
					'updated_at'       => $ahora
				));
		}
    }
}
