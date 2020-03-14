<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Detallehistoria extends Model
{
    use SoftDeletes;
    protected $table = 'detalle_historia';
    protected $dates = ['deleted_at'];

    public function historia()
    {
        return $this->belongsTo('App\Historia', 'id_historia');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'id_plan');
    }
    
}
