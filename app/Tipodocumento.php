<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipodocumento extends Model
{
	 use SoftDeletes;
    protected $table = 'tipodocumento';
    protected $dates = ['deleted_at'];
}
