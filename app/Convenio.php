<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Convenio extends Model
{
    use SoftDeletes;
    protected $table = 'convenio';
    protected $dates = ['deleted_at'];

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }
}
