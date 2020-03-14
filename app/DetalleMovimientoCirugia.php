<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleMovimientoCirugia extends Model
{
	use SoftDeletes;
    protected $table = 'detalle_cirugia';
    protected $dates = ['deleted_at'];

    public function movimiento()
    {
        return $this->belongsTo('App\MovimientoCirugia', 'cirugia_id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\Person', 'doctor_id');
    }

    public function responsableregistra(){
        return $this->belongsTo('App\Person','usuario_registro');
    }

    public function responsableActualiza(){
        return $this->belongsTo('App\Person','usuario_actualiza');
    }

    public function responsablePaga(){
        return $this->belongsTo('App\Person','usuario_pago');
    }
}
