<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Forma extends Model
{
	 use SoftDeletes;
    protected $table = 'forma';
    protected $dates = ['deleted_at'];
}
