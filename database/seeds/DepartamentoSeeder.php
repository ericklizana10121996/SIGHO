<?php

use Illuminate\Database\Seeder;

class DepartamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ahora         = new DateTime; 
		$departamentos = DB::connection('pgsql')->table('tb_departamento')->get();
		foreach ($departamentos as $key => $value) {
			DB::table('departamento')->insert(array(
					'id'        => $value->tb_departamento_id,
					'nombre'    => $value->tb_departamento_nombre,
					'created_at' => $ahora,
					'updated_at' => $ahora
				));
		}
    }
}
