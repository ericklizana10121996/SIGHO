<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Detallehojacosto extends Model
{
	 use SoftDeletes;
    protected $table = 'detallehojacosto';
    protected $dates = ['deleted_at'];

    public function hojacosto()
    {
        return $this->belongsTo('App\Hojacosto', 'hojacosto_id');
    }
   
    public function servicio()
    {
        return $this->belongsTo('App\Servicio', 'servicio_id');
    }

    public function persona()
    {
        return $this->belongsTo('App\Person', 'persona_id');
    }
}
