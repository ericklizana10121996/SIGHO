<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aperturacierrecaja extends Model
{
	 use SoftDeletes;
    protected $table = 'aperturacierrecaja';
    protected $dates = ['deleted_at'];

    public function person()
    {
        return $this->belongsTo('App\Person', 'person_id');
    }
}
