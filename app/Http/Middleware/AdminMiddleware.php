<?php

namespace App\Http\Middleware;

use Closure;
use GenTux\Jwt\GetsJwtToken;
use App\User;

class AdminMiddleware
{
    use GetsJwtToken;

    public function authUser()
    {
        if (!$this->jwtToken()->validate()) {
            return false;
        }
        $token = $this->jwtToken();
        $user = User::where('id', $token->payload('id'))->where('email', $token->payload('context.email'))->first();

        return $user;
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
        if($this->authUser()->isAdmin()){
            return $next($request);
        }
            return response()->json('You are not an admin!GO BACK');
        }

}
