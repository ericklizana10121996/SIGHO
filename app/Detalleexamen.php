<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Detalleexamen extends Model
{
	 use SoftDeletes;
    protected $table = 'detalleexamen';
    protected $dates = ['deleted_at'];

    public function examen()
    {
        return $this->belongsTo('App\Examen', 'examen_id');
    }
   
}
