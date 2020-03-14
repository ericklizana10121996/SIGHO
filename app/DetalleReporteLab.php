<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleReporteLab extends Model
{
	use SoftDeletes;
    protected $table = 'detalleReporteLab';
    protected $dates = ['deleted_at'];

    public function servicio()
    {
        return $this->belongsTo('App\Servicio', 'servicio_id');
    }

}
