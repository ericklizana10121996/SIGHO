<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribuidora extends Model
{
	 use SoftDeletes;
    protected $table = 'distribuidora';
    protected $dates = ['deleted_at'];
}
