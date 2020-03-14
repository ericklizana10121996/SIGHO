<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Principioactivo extends Model
{
	 use SoftDeletes;
    protected $table = 'principioactivo';
    protected $dates = ['deleted_at'];

}
