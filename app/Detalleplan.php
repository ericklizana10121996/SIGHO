<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Detalleplan extends Model
{
    use SoftDeletes;
    protected $table = 'detalleplan';
    protected $dates = ['deleted_at'];

    public function tiposervicio()
    {
        return $this->belongsTo('App\Tiposervicio', 'tiposervicio_id');
    }
}
