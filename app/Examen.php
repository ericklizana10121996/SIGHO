<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examen extends Model
{
	 use SoftDeletes;
    protected $table = 'examen';
    protected $dates = ['deleted_at'];

    public function tipoexamen()
    {
        return $this->belongsTo('App\Tipoexamen', 'tipoexamen_id');
    }

}
