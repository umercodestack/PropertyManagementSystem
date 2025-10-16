<?php
namespace App\Http\Middleware;

use Closure;
use App\Services\MixpanelService;
use Illuminate\Support\Facades\Auth;

class TrackPageVisits
{
    public function handle($request, Closure $next)
    {
        $mixpanelService = app(MixpanelService::class);

        // Authenticated user details
        $user = Auth::user();
        
         if ($user && $user->email === 'affiliate@livedin.me') {
        return $next($request); // Skip Mixpanel tracking
    }

        // Track Page View event with user details
        if ($user && $user->role_id === 2) {
        $mixpanelService->trackEvent('Page View', [
            'url' => $request->url(),
            'method' => $request->method(),
            'user_id' => $user ? $user->id : null, // Null if not authenticated
            'user_name' => $user ? $user->name : 'Guest',
            'user_email' => $user ? $user->email : 'Guest',
        ]);
      }    
            
        return $next($request);
    }
}