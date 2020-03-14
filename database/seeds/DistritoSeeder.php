<?php

use Illuminate\Database\Seeder;

class DistritoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ahora     = new DateTime; 
		$distritos = DB::connection('pgsql')->table('tb_distrito')->get();
		foreach ($distritos as $key => $value) {
			DB::table('distrito')->insert(array(
					'id'           => $value->tb_distrito_id,
					'nombre'       => $value->tb_distrito_nombre,
					'provincia_id' => $value->tb_provincia_id,
					'created_at'    => $ahora,
					'updated_at'    => $ahora
				));
		}
    }
}
