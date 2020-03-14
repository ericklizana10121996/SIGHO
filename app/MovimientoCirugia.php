<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class MovimientoCirugia extends Model
{
	use SoftDeletes;
    protected $table = 'movimiento_cirugia';
    protected $dates = ['deleted_at'];
    

    public function paciente()
    {
        return $this->belongsTo('App\Person', 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo('App\Person', 'medicoTratante_id');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }

    public function historia(){
        return $this->belongsTo('App\Historia', 'historia_id');
    }
}
