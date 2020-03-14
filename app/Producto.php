<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
	 use SoftDeletes;
    protected $table = 'producto';
    protected $dates = ['deleted_at'];

    

    public function laboratorio()
    {
        return $this->belongsTo('App\Laboratorio', 'laboratorio_id');
    }

    public function categoria()
    {
        return $this->belongsTo('App\Categoria', 'categoria_id');
    }

    public function presentacion()
    {
        return $this->belongsTo('App\Presentacion', 'presentacion_id');
    }

    public function proveedor()
    {
        return $this->belongsTo('App\Person', 'proveedor_id');
    }

    public function origen()
    {
        return $this->belongsTo('App\Origen', 'origen_id');
    }

    public function especialidadfarmacia()
    {
        return $this->belongsTo('App\Especialidadfarmacia', 'especialidadfarmacia_id');
    }

    public function anaquel()
    {
        return $this->belongsTo('App\Anaquel', 'anaquel_id');
    }

    public function condicionAlmacenamiento()
    {
        return $this->belongsTo('App\CondicionAlmacenamiento', 'condicionAlmac_id');
    }

    public function formaFarmaceutica()
    {
        return $this->belongsTo('App\FormaFarmaceutica', 'formaFarmac_id');
    }

    public function concentracion()
    {
        return $this->belongsTo('App\Concentracion', 'concentracion_id');
    }
  
}
