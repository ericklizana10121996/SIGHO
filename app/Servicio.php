<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
	 use SoftDeletes;
    protected $table = 'servicio';
    protected $dates = ['deleted_at'];
    
    public function tiposervicio()
    {
        return $this->belongsTo('App\Tiposervicio', 'tiposervicio_id');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }

    public function tarifario()
    {
        return $this->belongsTo('App\Tarifario', 'tarifario_id');
    }

}
