<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concentracion extends Model
{
	use SoftDeletes;
    protected $table = 'concentracion';
    protected $dates = ['deleted_at'];
}
