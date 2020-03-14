<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anaquel extends Model
{
	 use SoftDeletes;
    protected $table = 'anaquel';
    protected $dates = ['deleted_at'];
}
