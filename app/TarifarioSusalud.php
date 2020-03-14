<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TarifarioSusalud extends Model
{
	 use SoftDeletes;
    protected $table = 'tarifariosusalud';
    protected $dates = ['deleted_at'];
}
