<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sala extends Model
{
	 use SoftDeletes;
    protected $table = 'sala';
    protected $dates = ['deleted_at'];
}
