<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Detallelote extends Model
{
    use SoftDeletes;
    protected $table = 'detallelote';
    protected $dates = ['deleted_at'];
    
}
