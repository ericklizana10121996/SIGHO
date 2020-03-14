<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Motivo extends Model
{
    use SoftDeletes;
    protected $table = 'motivo';
    protected $dates = ['deleted_at'];

}
