<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cita extends Model
{
    use SoftDeletes;
    protected $table = 'Cita';
    protected $dates = ['deleted_at'];
    
    public function doctor()
    {
        return $this->belongsTo('App\Person', 'doctor_id');
    }
    
    public function paciente()
    {
        return $this->belongsTo('App\Person', 'paciente_id');
    }
    
    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }

    public function usuario2()
    {
        return $this->belongsTo('App\Person', 'usuario2_id');
    }
    
    public function anulacion()
    {
        return $this->belongsTo('App\Person', 'anulacion_id');
    }

    public function movimiento()
    {
        return $this->belongsTo('App\Movimiento', 'movimiento_id');
    }

    public function histori2()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }

}
