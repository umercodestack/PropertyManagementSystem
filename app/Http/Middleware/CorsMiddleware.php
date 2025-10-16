<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->getMethod() == "OPTIONS") {
            return response()->json([], 200);
        }
    
        // Need to test Cross Origin functionality
        $allowedOrigins = ['https://admin.livedin.co'];
        $origin = $request->headers->get('Origin');
        
        print_r($request->headers);
        echo '<br />';
        echo $origin;
        die;
        
        if (in_array($origin, $allowedOrigins)) {
            return $next($request)
            ->header('Access-Control-Allow-Origin', $origin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers');
        }

        return response('Forbidden', 403);
    }
}
