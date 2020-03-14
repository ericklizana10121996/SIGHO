<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleCirugiaParticular extends Model
{
	use SoftDeletes;
    protected $table = 'detalle_cirugiaparticular';
    protected $dates = ['deleted_at'];

    public function movimiento()
    {
        return $this->belongsTo('App\Movimiento', 'movimiento_id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\Person', 'doctor_id');
    }

    public function responsableRegistra(){
        return $this->belongsTo('App\Person','usuario_registro');
    }

    public function responsablePaga(){
        return $this->belongsTo('App\Person','usuario_pago');
    }
}
