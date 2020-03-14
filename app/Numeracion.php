<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Numeracion extends Model
{
    use SoftDeletes;
    protected $table = 'Numeracion';
    protected $dates = ['deleted_at'];

    public function tipodocumento()
    {
        return $this->belongsTo('App\Tipodocumento', 'tipodocumento_id');
    }
    
    public function scopeNumeroSigue($query,$tipomovimiento_id,$tipodocumento_id=0,$serie=0,$manual='S'){
        if($tipodocumento_id==0){
            if($serie==0){
                $rs=$query->where('tipomovimiento_id','=',$tipomovimiento_id)->where('manual','like',$manual)->select(DB::raw("(CASE WHEN numero IS NULL THEN 0 ELSE numero END)*1 AS maximo"))->first();
            }else{
                $rs=$query->where('tipomovimiento_id1','=',$tipomovimiento_id)->where('manual','like',$manual)->where('serie','=',$serie)->select(DB::raw("(CASE WHEN numero IS NULL THEN 0 ELSE numero END)*1 AS maximo"))->first();   
            }
        }else{
            if($serie==0){
                $rs=$query->where('tipomovimiento_id','=',$tipomovimiento_id)->where('manual','like',$manual)->where('tipodocumento_id','=',$tipodocumento_id)->select(DB::raw("(CASE WHEN numero IS NULL THEN 0 ELSE numero END)*1 AS maximo"))->first();
            }else{
                $rs=$query->where('tipomovimiento_id','=',$tipomovimiento_id)->where('manual','like',$manual)->where('tipodocumento_id','=',$tipodocumento_id)->where('serie','=',$serie)->select(DB::raw("(CASE WHEN numero IS NULL THEN 0 ELSE numero END)*1 AS maximo"))->first();
            }
        }
        return str_pad($rs->maximo+1,8,'0',STR_PAD_LEFT);    
    }
}
