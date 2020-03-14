<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menuoptioncategory extends Model
{
	use SoftDeletes;
    protected $table = 'menuoptioncategory';
    protected $dates = ['deleted_at'];

    public function menuoptions()
	{
		return $this->hasMany('App\Menuoption');
	}

	public function Fathercategory()
	{
		return $this->belongsTo('App\Menuoptioncategory', 'menuoptioncategory_id');
	}

	public function Soncategory()
	{
		return $this->hasMany('App\Menuoptioncategory', 'menuoptioncategory_id');
	}

	public function scopelistar($query, $name)
    {
        return $query->where(function($subquery) use($name)
		            {
		            	if (!is_null($name)) {
		            		$subquery->where('name', 'LIKE', '%'.$name.'%');
		            	}
		            })
        			->orderBy('menuoptioncategory_id', 'ASC')
        			->orderBy('order', 'ASC');
    }
}
