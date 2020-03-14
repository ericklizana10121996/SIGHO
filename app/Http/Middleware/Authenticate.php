<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->guest()) {
            if ($request->ajax()) {
                if($request->url()!="http://192.168.1.118/juanpablo/producto/vistamedico" && $request->url()!="http://192.168.1.118/juanpablo/producto/cie10"){
                    return response('Unauthorized. '.$request->url(), 401);
                }
            } else {
                return redirect()->guest('auth/login');
            }
        }

        return $next($request);
    }
}
