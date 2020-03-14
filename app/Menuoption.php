<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menuoption extends Model
{
    protected $table = 'menuoption';
    protected $dates = ['deleted_at'];

	public function menuoptioncategory()
	{
		return $this->belongsTo('App\Menuoptioncategory', 'menuoptioncategory_id');
	}

	/**
	 * Método para listar las opciones de menu
	 * @param  [type] $query [description]
	 * @return [type]        [description]
	 */
	public function scopelistar($query, $name, $menuoptioncategory_id)
    {
        return $query->where(function($subquery) use($name)
		            {
		            	if (!is_null($name)) {
		            		$subquery->where('name', 'LIKE', '%'.$name.'%');
		            	}
		            })
        			->where(function($subquery) use($menuoptioncategory_id)
		            {
		            	if (!is_null($menuoptioncategory_id)) {
		            		$subquery->where('menuoptioncategory_id', '=', $menuoptioncategory_id);
		            	}
		            })
        			->orderBy('menuoptioncategory_id', 'ASC')
        			->orderBy('order', 'ASC');
    }

}
