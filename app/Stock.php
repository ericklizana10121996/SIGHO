<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
	 use SoftDeletes;
    protected $table = 'stock';
    protected $dates = ['deleted_at'];

    public function producto()
    {
        return $this->belongsTo('App\Producto', 'producto_id');
    }
}
