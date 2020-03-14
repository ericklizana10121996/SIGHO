<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tiposervicio extends Model
{
	 use SoftDeletes;
    protected $table = 'tiposervicio';
    protected $dates = ['deleted_at'];
}
