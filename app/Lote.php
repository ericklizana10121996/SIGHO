<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
	 use SoftDeletes;
    protected $table = 'lote';
    protected $dates = ['deleted_at'];

    public function producto()
    {
        return $this->belongsTo('App\Distribuidora', 'producto_id');
    }

    public function almacen()
    {
        return $this->belongsTo('App\Laboratorio', 'almacen_id');
    }

}
