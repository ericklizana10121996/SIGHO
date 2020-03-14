<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Detalleanalisis extends Model
{
	 use SoftDeletes;
    protected $table = 'detalleanalisis';
    protected $dates = ['deleted_at'];

    public function detalleexamen()
    {
        return $this->belongsTo('App\Detalleexamen', 'detalleexamen_id');
    }
    public function analisis()
    {
        return $this->belongsTo('App\Analisis', 'analisis_id');
    }
   
}
