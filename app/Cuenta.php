<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cuenta extends Model
{
	 use SoftDeletes;
    protected $table = 'cuenta';
    protected $dates = ['deleted_at'];

    public function movimiento()
    {
        return $this->belongsTo('App\Movimiento', 'movimiento_id');
    }

}
