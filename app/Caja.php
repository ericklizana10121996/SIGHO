<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
	 use SoftDeletes;
    protected $table = 'caja';
    protected $dates = ['deleted_at'];
}
