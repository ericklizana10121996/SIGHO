<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call(UserTableSeeder::class);
        $this->call(DepartamentoSeeder::class);
        $this->call(ProvinciaSeeder::class);
        $this->call(DistritoSeeder::class);
        $this->call(PersonSeeder::class);
        $this->call(WorkertypeSeeder::class);
        $this->call(MenuoptioncategorySeeder::class);
        $this->call(MenuoptionSeeder::class);
        $this->call(UsertypeSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(UserSeeder::class);

        Model::reguard();
    }
}
