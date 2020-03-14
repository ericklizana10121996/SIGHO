<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Origen extends Model
{
	 use SoftDeletes;
    protected $table = 'origen';
    protected $dates = ['deleted_at'];
}
