<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipohabitacion extends Model
{
	 use SoftDeletes;
    protected $table = 'tipohabitacion';
    protected $dates = ['deleted_at'];
}
