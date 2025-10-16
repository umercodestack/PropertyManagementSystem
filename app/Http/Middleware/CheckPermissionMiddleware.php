<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;   

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        
        $action = Route::currentRouteAction(); // Get the full action (controller@method)
    $method = explode('@', $action)[1] ?? ''; // Extract the method name (e.g., 'store', 'update')

    // Skip middleware logic for 'store' and 'update' methods
    if (in_array($method, ['store', 'update','updateData','storeComment'])) {
        return $next($request);
    }
        
        $routeName = Route::current()->getName();
        //dd($routeName);
        if (in_array($routeName, ['listing.occupancyData','listing.otaOccupancyData'])) {
            return $next($request);
        }
        
        
        $user = Auth::user();
        if ($user->role->role_name == 'Super Admin' || $user->role->role_name == 'Admin' || ($user && $user->hasPermission($routeName))) {
            return $next($request);
        }

        abort(403, 'Unauthorized');
    }
}
