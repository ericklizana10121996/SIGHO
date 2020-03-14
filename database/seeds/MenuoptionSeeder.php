<?php

use Illuminate\Database\Seeder;

class MenuoptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = new DateTime;

		$menuoptioncategory_id = DB::table('menuoptioncategory')->where('name', '=', 'Usuarios')->first()->id;

		$datos = array(
				array(
					'name' => 'Categoría de opción de menu',
					'link'   => 'categoriaopcionmenu'
				),
				array(
					'name' => 'Opción de menu',
					'link'   => 'opcionmenu'
				),
				array(
					'name' => 'Tipos de usuario',
					'link'   => 'tipousuario'
				),
				array(
					'name' => 'Usuario',
					'link'   => 'usuario'
				)
			);

		for ($i=0; $i < count($datos); $i++) { 
			DB::table('menuoption')->insert(array(
					'name'                 => $datos[$i]['name'],
					'link'                   => $datos[$i]['link'],
					'order'                  => $i+1,
					'menuoptioncategory_id' => $menuoptioncategory_id,
					'created_at'             => $now,
					'updated_at'             => $now
				)
			);
		}

		$menuoptioncategory_id = DB::table('menuoptioncategory')->where('name', '=', 'Administración')->first()->id;

		$datos = array(
				array(
					'name' => 'Tipo de cambio',
					'link'   => 'changetype'
				),
				array(
					'name' => 'Categorias',
					'link'   => 'category'
				),
				array(
					'name' => 'Marcas',
					'link'   => 'mark'
				),
				array(
					'name' => 'Unidades',
					'link'   => 'unit'
				),
				array(
					'name' => 'Productos',
					'link'   => 'product'
				)
			);

		for ($i=0; $i < count($datos); $i++) { 
			DB::table('menuoption')->insert(array(
					'name'                 => $datos[$i]['name'],
					'link'                   => $datos[$i]['link'],
					'order'                  => $i+1,
					'menuoptioncategory_id' => $menuoptioncategory_id,
					'created_at'             => $now,
					'updated_at'             => $now
				)
			);
		}

		$menuoptioncategory_id = DB::table('menuoptioncategory')->where('name', '=', 'Personas')->first()->id;

		$datos = array(
				array(
					'name' => 'Tipo trabajador',
					'link'   => 'workertype'
				),
				array(
					'name' => 'Clientes',
					'link'   => 'customer'
				),
				array(
					'name' => 'Proveedores',
					'link'   => 'provider'
				),
				array(
					'name' => 'Trabajadores',
					'link'   => 'employee'
				)
			);

		for ($i=0; $i < count($datos); $i++) { 
			DB::table('menuoption')->insert(array(
					'name'                 => $datos[$i]['name'],
					'link'                   => $datos[$i]['link'],
					'order'                  => $i+1,
					'menuoptioncategory_id' => $menuoptioncategory_id,
					'created_at'             => $now,
					'updated_at'             => $now
				)
			);
		}
    }
}
