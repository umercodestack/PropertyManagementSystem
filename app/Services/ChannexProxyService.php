<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChannexProxyService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('CHANNEX_API_KEY');
        $this->baseUrl = env('CHANNEX_URL');
    }

    public function postToProxy($channelId, $data)
    {
        $url = "{$this->baseUrl}/api/v1/channels/{$channelId}/action/api_proxy";
        $response = Http::withHeaders([
            'user-api-key' => $this->apiKey,
        ])->post($url, $data);

        return $response;
    }
}
