<?php

namespace App\Http\Controllers\Api\Mixpanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\MixpanelService; 
use Illuminate\Support\Facades\Auth;
use App\Utilities\UserUtility;

class MixpanelController extends Controller
{
    protected $mixpanelService;

    public function __construct(MixpanelService $mixpanelService)
    {
        $this->mixpanelService = $mixpanelService;
    }

    public function index() 
    {
        //
    }


    public function changelanguage()
    {

        $user = Auth::user();

        try {
            
             $userUtility = new UserUtility();
            $location = $userUtility->getUserGeolocation();
            
            $this->mixpanelService->trackEvent('Change language', [
                'distinct_id' => $user->id,
                'first_name' => $user->name,
                'last_name' => $user->surname,
                'email' => $user->email,
                '$country' => $location['country'],
                '$region' => $location['region'],
                '$city' => $location['city'],
                '$os' => $userUtility->getUserOS(), // Add OS here
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'timezone' => $location['timezone'],
                'ip_address' => $location['ip'],
                'db_country' => $user->country,
                'db_city' => $user->city
            ]);


            $this->mixpanelService->setPeopleProperties($user->id, [
                '$first_name' => $user->name,
                '$last_name' => $user->surname,
                '$email' => $user->email,
                '$country' => $location['country'],
                '$region' => $location['region'],
                '$city' => $location['city'],
                '$os' => $userUtility->getUserOS(), // Add OS here
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'timezone' => $location['timezone'],
                'ip_address' => $location['ip'],
                'db_country' => $user->country,
                'db_city' => $user->city
               
            ]);

        } catch (\Exception $e) {
            
            
        }

        return response()->json(['message' =>"Success"], 200);
    }

    private function getUserOS()
    {
       $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
      
        $osArray = [
            'Windows' => 'Windows',
            'Macintosh' => 'Mac OS',
            'iPhone' => 'iOS',  
            'iPad' => 'iOS',     
            'Android' => 'Android',
            'Linux' => 'Linux',
            'PostmanRuntime' => 'Postman',
            'okhttp' => 'Android',      
        ];
    
        
        if (stripos($userAgent, 'LivedInMobileApp') !== false && stripos($userAgent, 'CFNetwork') !== false && stripos($userAgent, 'Darwin') !== false) {
             
             return 'iOS';
        }
    
       
        foreach ($osArray as $key => $os) {
            if (stripos($userAgent, $key) !== false) {
                return $os;
            }
        }
    
        
        return 'Unknown';
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
