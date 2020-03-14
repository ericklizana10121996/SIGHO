<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Detallemovcaja extends Model
{
	 use SoftDeletes;
    protected $table = 'detallemovcaja';
    protected $dates = ['deleted_at'];

    public function movimiento()
    {
        return $this->belongsTo('App\Movimiento', 'movimiento_id');
    }

    public function cuenta()
    {
        return $this->belongsTo('App\Movimiento', 'cuenta_id');
    }
    
    public function servicio()
    {
        return $this->belongsTo('App\Servicio', 'servicio_id');
    }

    public function persona()
    {
        return $this->belongsTo('App\Person', 'persona_id');
    }
    
    public function socio()
    {
        return $this->belongsTo('App\Person', 'medicosocio_id');
    }

    public function area()
    {
        return $this->belongsTo('App\Area', 'area_id');
    }
}
