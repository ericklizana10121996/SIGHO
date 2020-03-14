<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use App\Http\Requests;
use App\Person;
use App\Rolpersona;
use App\Librerias\Libreria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    public function employeesautocompleting($searching)
    {
        $entidad    = 'Person';
        $mdlPerson = new Person();
        $resultado = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','5')->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->whereNull('person.deleted_at')->orderBy('apellidopaterno', 'ASC')->select('rolpersona.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => $value->person->nombres.' '.$value->person->apellidopaterno.' '.$value->person->apellidomaterno,
                            'id'    => $value->person->id,
                            'value' => $value->person->nombres.' '.$value->person->apellidopaterno.' '.$value->person->apellidomaterno,
                        );
        }
        return json_encode($data);
    }

    public function customersautocompleting($searching)
    {
        $entidad   = 'Person';
        $mdlPerson = new Person();
        $resultado = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','3')->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->whereNull('person.deleted_at')->orderBy('person.bussinesname', 'ASC')->select('rolpersona.*');
        $lista     = $resultado->get();
        $data      = array();
        foreach ($lista as $key => $value) {
            $data[] = array(
                            'label' => $value->person->nombres.' '.$value->person->apellidopaterno.' '.$value->person->apellidomaterno,
                            'id'    => $value->person->id,
                            'value' => $value->person->nombres.' '.$value->person->apellidopaterno.' '.$value->person->apellidomaterno,
                        );
            
        }
        return json_encode($data);
    }

    public function providersautocompleting($searching)
    {
        $entidad    = 'Person';
        $mdlPersona = new Person();
        $resultado = Rolpersona::join('person','rolpersona.person_id','=','person.id')
                            ->where('rol_id','=','2')->where(DB::raw('concat(person.ruc,\' \',person.bussinesname)'), 'LIKE', '%'.strtoupper($searching).'%')->whereNull('person.deleted_at')->orderBy('person.bussinesname', 'ASC')->select('rolpersona.*');
        //$resultado  = Person::where('type', '=', 'C')->where('bussinesname', 'LIKE', '%'.strtoupper($searching).'%')->orWhere(DB::raw("CONCAT(lastname,' ',firstname)"),'LIKE' ,'%'.strtoupper($searching).'%');
        $lista      = $resultado->get();
        $data       = array();
        foreach ($lista as $key => $value) {
            $data[] = array(
                            'label' => $value->person->bussinesname,
                            'id'    => $value->person->id,
                            'value' => $value->person->bussinesname,
                            'ruc' => $value->person->ruc,
                        );
            
        }
        return json_encode($data);
    }

    public function doctorautocompleting($searching)
    {
        $entidad    = 'Person';
        $mdlPerson = new Person();
        $resultado = Person::join('especialidad','especialidad.id','=','person.especialidad_id')
                            ->where('workertype_id','=','1')->where(DB::raw('CONCAT(apellidopaterno," ",apellidomaterno," ",nombres)'), 'LIKE', '%'.strtoupper($searching).'%')->orderBy('apellidopaterno', 'ASC')->select('person.*');
        $list      = $resultado->get();
        $data = array();
        foreach ($list as $key => $value) {
            $data[] = array(
                            'label' => $value->nombres.' '.$value->apellidopaterno.' '.$value->apellidomaterno,
                            'id'    => $value->id,
                            'value' => $value->nombres.' '.$value->apellidopaterno.' '.$value->apellidomaterno,
                        );
        }
        return json_encode($data);
    }

}
