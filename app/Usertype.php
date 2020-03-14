<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usertype extends Model
{
	use SoftDeletes;
    protected $table = 'usertype';
    protected $dates = ['deleted_at'];

    /**
     * Método para listar
     * @param  model $query modelo
     * @param  string $name  nombre
     * @return sql        sql
     */
    public function scopelistar($query, $name)
    {
        return $query->where(function($subquery) use($name)
		            {
		            	if (!is_null($name)) {
		            		$subquery->where('name', 'LIKE', '%'.$name.'%');
		            	}
		            })
        			->orderBy('name', 'ASC');
    }

    /**
     * Método que retorna los usuarios con el tipo de usuario indicado
     * @return sql sql
     */
    public function users()
	{
		return $this->hasMany('App\User');
	}

	/**
	 * Método de que retorna todos los permisos para el tpo de usuario indicado
	 * @return sql sql
	 */
	public function permissions()
	{
		return $this->hasMany('App\Permission');
	}

	/**
	 * Método que hace una relación de muchos a muchos, y que devuelve todas las opciones de menu de un tipo de usuario
	 * @return sql sql
	 */
	public function menuoptions(){
		return $this->belongsToMany('App\Menuoption', 'permission', 'usertype_id', 'menuoption_id');
	}
}
