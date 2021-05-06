<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class jobseekermiddleware
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
        if($user_type == 2)
        {
            $success = [
                'heading' => "JobSeekers Origin",
                'msg' => "Only JobSeekers have an access for this",
            ];

            return response()->json(["success"=>$success],401);
        }
        return $next($request);
    }
}
