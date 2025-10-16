<?php

namespace App\Utilities;

class UserUtility
{
    public function getUserOS()
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

    public function getUserGeolocation()
    {
        $ip = $this->getClientIP();

        if ($ip == '127.0.0.1' || $ip == '::1') {
            $ip = '8.8.8.8';
        }

        $geoApiUrl = "http://ip-api.com/json/{$ip}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $geoApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $geoData = json_decode($response, true);

            if ($geoData && $geoData['status'] === 'success') {
                return [
                    'ip' => $ip,
                    'country' => $geoData['country'] ?? 'Unknown',
                    'region' => $geoData['regionName'] ?? 'Unknown',
                    'city' => $geoData['city'] ?? 'Unknown',
                    'latitude' => $geoData['lat'] ?? 'Unknown',
                    'longitude' => $geoData['lon'] ?? 'Unknown',
                    'timezone' => $geoData['timezone'] ?? 'Unknown'
                ];
            }
        }

        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'region' => 'Unknown',
            'city' => 'Unknown',
            'latitude' => 'Unknown',
            'longitude' => 'Unknown',
            'timezone' => 'Unknown',
        ];
    }

    public function getClientIP()
    {
        $ip = null;

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }

        return 'Unknown';
    }
}
