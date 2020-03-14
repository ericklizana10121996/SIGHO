<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Salaoperacion extends Model
{
    use SoftDeletes;
    protected $table = 'Salaoperacion';
    protected $dates = ['deleted_at'];
    
    public function medico()
    {
        return $this->belongsTo('App\Person', 'medico_id');
    }
    
    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }
    
    public function sala()
    {
        return $this->belongsTo('App\Sala', 'sala_id');
    }
    
    public function tipohabitacion()
    {
        return $this->belongsTo('App\Tipohabitacion', 'tipohabitacion_id');
    }
    
    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }

    public function usuario2()
    {
        return $this->belongsTo('App\Person', 'usuario2_id');
    }
}
