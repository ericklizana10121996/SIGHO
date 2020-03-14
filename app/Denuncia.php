<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Denuncia extends Model
{
    use SoftDeletes;
    protected $table = 'Denuncia';
    protected $dates = ['deleted_at'];
    
    public function historia()
    {
        return $this->belongsTo('App\Historia', 'historia_id');
    }

    public function usuario()
    {
        return $this->belongsTo('App\Person', 'usuario_id');
    }

    public function docgarantia()
    {
        return $this->belongsTo('App\Movimiento', 'garantia');
    }
}
