<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Habitacion extends Model
{
	 use SoftDeletes;
    protected $table = 'habitacion';
    protected $dates = ['deleted_at'];
    
    public function tipohabitacion()
    {
        return $this->belongsTo('App\Tipohabitacion', 'tipohabitacion_id');
    }

    public function piso()
    {
        return $this->belongsTo('App\Piso', 'piso_id');
    }

    public function hospitalizacion()
    {
        return $this->belongsTo('App\Hospitalizacion', 'hospitalizacion_id');
    }

}
