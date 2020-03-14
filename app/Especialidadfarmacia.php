<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidadfarmacia extends Model
{
	 use SoftDeletes;
    protected $table = 'especialidadfarmacia';
    protected $dates = ['deleted_at'];
}
