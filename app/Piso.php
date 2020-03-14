<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Piso extends Model
{
	 use SoftDeletes;
    protected $table = 'piso';
    protected $dates = ['deleted_at'];
}
