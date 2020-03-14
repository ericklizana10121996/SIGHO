<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conveniofarmacia extends Model
{
    use SoftDeletes;
    protected $table = 'conveniofarmacia';
    protected $dates = ['deleted_at'];

    public function plan()
    {
        return $this->belongsTo('App\Plan', 'plan_id');
    }
}
