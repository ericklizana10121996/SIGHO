<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Lotetrama extends Model
{
    use SoftDeletes;
    protected $table = 'lotetrama';
    protected $dates = ['deleted_at'];
    
}
