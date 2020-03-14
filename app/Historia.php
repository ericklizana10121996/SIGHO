<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Historia extends Model
{
    use SoftDeletes;
    protected $table = 'historia';
    protected $dates = ['deleted_at'];
    
    public function persona()
    {
        return $this->belongsTo('App\Person', 'person_id');
    }
    
    public function convenio()
    {
        return $this->belongsTo('App\Convenio', 'convenio_id');
    }

    public function departamento()
    {
        return $this->belongsTo('App\Departamento', 'departamento');
    }
    public function provincia()
    {
        return $this->belongsTo('App\Provincia', 'provincia');
    }
    public function distrito()
    {
        return $this->belongsTo('App\Distrito', 'distrito');
    }
    
    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }

    public function scopeNumeroSigue($query){
        $rs=$query->select(DB::raw("max((CASE WHEN numero IS NULL THEN 0 ELSE numero END)*1) AS maximo"))->first();
        return str_pad($rs->maximo+1,8,'0',STR_PAD_LEFT);    
    }

}
