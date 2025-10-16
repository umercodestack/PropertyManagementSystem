<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckIfUserBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user_id = $request->header('X-User-Id');

        if(!empty($user_id)){

            $user = User::find($user_id);
            if (!$user || $user->is_block == 1) {
                return response()->json(['error' => 'User is blocked'], 401);
            }
        }

        return $next($request);
    }
}
