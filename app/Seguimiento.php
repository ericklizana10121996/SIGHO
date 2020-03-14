<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Seguimiento extends Model
{
    use SoftDeletes;
    protected $table = 'seguimiento';
    protected $dates = ['deleted_at'];
    
    public function personaenvio()
    {
        return $this->belongsTo('App\Person', 'personaenvio_id');
    }

    public function personarecepcion()
    {
        return $this->belongsTo('App\Person', 'personarecepcion_id');
    }
    
    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }

    public function areaenvio()
    {
        return $this->belongsTo('App\Area', 'areaenvio_id');
    }

    public function areadestino()
    {
        return $this->belongsTo('App\Area', 'areadestino_id');
    }

}
