<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormaFarmaceutica extends Model
{
	use SoftDeletes;
    protected $table = 'formaFarmaceutica';
    protected $dates = ['deleted_at'];
}
