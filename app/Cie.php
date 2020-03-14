<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cie extends Model
{
    use SoftDeletes;
    protected $table = 'Cie';
    protected $dates = ['deleted_at'];
    
}
