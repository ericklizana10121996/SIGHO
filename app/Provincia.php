<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $table = 'provincia';
    protected $dates = ['deleted_at'];

     /**
     * método para obtener las distritos hijas
     * @return [type] [description]
     */
    public function distritos()
	{
		return $this->hasMany('App\Distrito');
	}
}
