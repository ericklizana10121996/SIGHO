<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipoexamen extends Model
{
	 use SoftDeletes;
    protected $table = 'tipoexamen';
    protected $dates = ['deleted_at'];
}
