<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
	 use SoftDeletes;
    protected $table = 'unidad';
    protected $dates = ['deleted_at'];
}
