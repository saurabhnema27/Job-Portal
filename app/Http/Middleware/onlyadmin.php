<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class onlyadmin
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
        $user = Auth::user();
        // dd($user_type);
        if($user->user_type != 3)
        {
            $success = [
                'heading' => "Admin Origin",
                'msg' => "Only Admin have an access for this",
            ];

            return response()->json(["success"=>$success],401);
        }
        return $next($request);
    }
}
