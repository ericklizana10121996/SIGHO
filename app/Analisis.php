<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Analisis extends Model
{
    use SoftDeletes;
    protected $table = 'Analisis';
    protected $dates = ['deleted_at'];
    
    
    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }

}
