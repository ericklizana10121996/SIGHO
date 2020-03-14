<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kardex extends Model
{
	 use SoftDeletes;
    protected $table = 'kardex';
    protected $dates = ['deleted_at'];

    public function detallemovimiento()
    {
        return $this->belongsTo('App\Detallemovimiento', 'detallemovimiento_id');
    }

    public function lote()
    {
        return $this->belongsTo('App\Distribuidora', 'lote_id');
    }

}
