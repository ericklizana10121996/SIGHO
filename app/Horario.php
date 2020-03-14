<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horario extends Model
{
	 use SoftDeletes;
    protected $table = 'horario';
    protected $dates = ['deleted_at'];

    public function person(){
        return $this->belongsTo('App\Person', 'person_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }
}
