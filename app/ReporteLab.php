<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReporteLab extends Model
{
	use SoftDeletes;
    protected $table = 'reportelab';
    protected $dates = ['deleted_at'];

    public function responsable()
    {
        return $this->belongsTo('App\Person', 'responsable_id');
    }

}
