<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipomovimiento extends Model
{
	 use SoftDeletes;
    protected $table = 'tipomovimiento';
    protected $dates = ['deleted_at'];
}
