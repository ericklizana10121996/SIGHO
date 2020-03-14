<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Productoprincipio extends Model
{
	 use SoftDeletes;
    protected $table = 'productoprincipio';
    protected $dates = ['deleted_at'];

    public function producto()
    {
        return $this->belongsTo('App\Producto', 'producto_id');
    }

    public function principioactivo()
    {
        return $this->belongsTo('App\Principioactivo', 'principioactivo_id');
    }
}
