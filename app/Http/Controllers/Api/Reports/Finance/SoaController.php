<?php

namespace App\Http\Controllers\Api\Reports\Finance;

use App\Http\Controllers\Controller;
use App\Models\ReportFinanceSoa;
use App\Models\ReportFinanceSoaPop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\MixpanelService; 
use App\Models\User;
use App\Utilities\UserUtility;

class SoaController extends Controller
{
    
    
    protected $mixpanelService;

    public function __construct(MixpanelService $mixpanelService)
    {
        $this->mixpanelService = $mixpanelService;
    }
    
    public function index($user_id)
    {
        $userDB = User::whereId($user_id)->first();
        // dd($userDB);
        
        if (!empty($userDB->role_id) && $userDB->role_id === 2) {
           
            try {
                
                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();
                
                $this->mixpanelService->trackEvent('Payment Module Open', [
                    'distinct_id' => $userDB->id,
                    'first_name' => $userDB->name,
                    'last_name' => $userDB->surname,
                    'email' => $userDB->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $userDB->country,
                    'db_city' => $userDB->city
                ]);
                
                $this->mixpanelService->setPeopleProperties($userDB->id, [
                    '$first_name' => $userDB->name,
                    '$last_name' => $userDB->surname,
                    '$email' => $userDB->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $userDB->country,
                    'db_city' => $userDB->city
                   
                ]);
                
                
            } catch (\Exception $e) {
                
                Log::error('Error tracking Mixpanel event.', [
                    'user_id' => $userDB->id,
                    'error_message' => $e->getMessage()
                ]);
            }
        }
        
        $soaReports = ReportFinanceSoa::where('user_id', $user_id)->get();
        return response($soaReports);
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

    public function downloadSoa($id)
    {
        
        
        $user_id = Auth::id();
        $userDB = User::find($user_id);
        
        if (!empty($userDB->role_id) && $userDB->role_id === 2) {
           
                try {
    
    
                    $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();
    
                    $this->mixpanelService->trackEvent('Statement of Account Downloaded', [
                        'distinct_id' => $userDB->id,
                        'first_name' => $userDB->name,
                        'last_name' => $userDB->surname,
                        'email' => $userDB->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $userDB->country,
                        'db_city' => $userDB->city,
                        'host_type' => $userDB->hostType->module_name,
                        'Report_Date' =>  $soa->publish_date
                    ]);
    
                    $this->mixpanelService->setPeopleProperties($userDB->id, [
                        '$first_name' => $userDB->name,
                        '$last_name' => $userDB->surname,
                        '$email' => $userDB->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $userDB->country,
                        'db_city' => $userDB->city,
                        'host_type' => $userDB->hostType->module_name,
                        'Report_Date' =>  $soa->publish_date

                       
                    ]);
    
    
                } catch (\Exception $e) {
                    
                    
                }
            }

            if (!empty($userDB->role_id) && $userDB->role_id === 2) {
           
                try {
    
    
                    $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();
    
                    $this->mixpanelService->trackEvent('Proof of Payment Downloaded', [
                        'distinct_id' => $userDB->id,
                        'first_name' => $userDB->name,
                        'last_name' => $userDB->surname,
                        'email' => $userDB->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $userDB->country,
                        'db_city' => $userDB->city,
                        'host_type' => $userDB->hostType->module_name,
                        'Report_Date' =>  $soa->publish_date
                    ]);
    
                    $this->mixpanelService->setPeopleProperties($userDB->id, [
                        '$first_name' => $userDB->name,
                        '$last_name' => $userDB->surname,
                        '$email' => $userDB->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $userDB->country,
                        'db_city' => $userDB->city,
                        'host_type' => $userDB->hostType->module_name,
                        'Report_Date' =>  $soa->publish_date

                       
                    ]);
    
    
                } catch (\Exception $e) {
                    
                    
                }
            }   
            
        
        $soa = ReportFinanceSoa::where('id', $id)->first();
        $soaPop = ReportFinanceSoaPop::where('soa_id', $id)->first();
    
        // Generate the full URL for the soa file
        $soaFile = $soa && $soa->file_path 
            ? url(Storage::url($soa->file_path)) 
            : 'https://livedin.co/';
    
        // Generate the full URL for the pop file
        $soaPopFile = $soaPop && $soaPop->file_path 
            ? url(Storage::url($soaPop->file_path)) 
            : 'https://livedin.co/';
            
         
    
        return response(['soa' => $soaFile, 'pop' => $soaPopFile]);
    }
}
