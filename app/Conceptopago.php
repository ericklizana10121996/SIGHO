<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conceptopago extends Model
{
	 use SoftDeletes;
    protected $table = 'conceptopago';
    protected $dates = ['deleted_at'];
}
