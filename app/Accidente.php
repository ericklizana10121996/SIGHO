<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accidente extends Model
{
	 use SoftDeletes;
    protected $table = 'accidente';
    protected $dates = ['deleted_at'];

    public function person(){
        return $this->belongsTo('App\Person', 'persona_id');
    }

    public function usuario(){
        return $this->belongsTo('App\Person', 'usuario_id');
    }

    public function convenio(){
        return $this->belongsTo('App\Convenio', 'convenio_id');
    }
}
