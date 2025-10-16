<?php

namespace App\Services;

use Mixpanel;

class MixpanelService
{
    protected $mixpanel;

    public function __construct()
    {
        $this->mixpanel = Mixpanel::getInstance(env('MIXPANEL_TOKEN'));
    }

    public function trackEvent($event, $properties = [])
    {
        $this->mixpanel->track($event, $properties);
    }

    public function identifyUser($userId, $properties = [])
    {
        $this->mixpanel->identify($userId);
        $this->mixpanel->people->set($userId, $properties);
    }
    
    
    public function setPeopleProperties($distinctId, $properties = [])
    {
        try {
                $this->mixpanel->people->set($distinctId, $properties);
        } catch (\Exception $e) {
                Log::error('Error setting Mixpanel People properties.', [
                    'distinct_id' => $distinctId,
                    'properties' => $properties,
                    'error_message' => $e->getMessage(),
                ]);
        }
    }
}