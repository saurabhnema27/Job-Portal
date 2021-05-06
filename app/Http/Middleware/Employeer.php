<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Employeer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user_type = Auth::user()->user_type;
        if($user_type == 1)
        {
            $success = [
                'heading' => "Employeer Origin",
                'msg' => "Only Employeer have an access for this",
            ];

            return response()->json(["success"=>$success],401);
        }
        return $next($request);
    }
}
