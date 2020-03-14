<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presentacion extends Model
{
	 use SoftDeletes;
    protected $table = 'presentacion';
    protected $dates = ['deleted_at'];
}
