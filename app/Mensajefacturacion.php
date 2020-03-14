<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mensajefacturacion extends Model
{
	 use SoftDeletes;
    protected $table = 'mensajefacturacion';
    protected $dates = ['deleted_at'];

    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }

    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }
}
