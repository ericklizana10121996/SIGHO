<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tarifario extends Model
{
	 use SoftDeletes;
    protected $table = 'tarifario';
    protected $dates = ['deleted_at'];
}
