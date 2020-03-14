<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hospitalizacion extends Model
{
	 use SoftDeletes;
    protected $table = 'hospitalizacion';
    protected $dates = ['deleted_at'];
    
    public function habitacion()
    {
        return $this->belongsTo('App\Habitacion', 'habitacion_id');
    }
    
    public function medico()
    {
        return $this->belongsTo('App\Person', 'medico_id');
    }
    
    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }
    
    public function usuarioalta()
    {
        return $this->belongsTo('App\Person', 'usuarioalta_id');
    }

    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }

    public function tipoalta()
    {
        return $this->belongsTo('App\Tipoalta', 'tipoalta_id', "idtipoalta", 'idtipoalta');
    }

}
