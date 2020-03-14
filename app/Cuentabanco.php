<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cuentabanco extends Model
{
    use SoftDeletes;
    protected $table = 'Cuentabanco';
    protected $dates = ['deleted_at'];
    
    public function banco()
    {
        return $this->belongsTo('App\Banco', 'banco_id');
    }
}
