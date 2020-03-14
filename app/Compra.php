<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
	 use SoftDeletes;
    protected $table = 'movimiento';
    protected $dates = ['deleted_at'];

    public function tipodocumento()
    {
        return $this->belongsTo('App\Tipodocumento', 'tipodocumento_id');
    }

    public function tipomovimiento()
    {
        return $this->belongsTo('App\Tipomovimiento', 'tipomovimiento_id');
    }

    public function person()
    {
        return $this->belongsTo('App\Person', 'persona_id');
    }

    public function doctor()
    {
        return $this->belongsTo('App\Person', 'doctor_id');
    }
}
