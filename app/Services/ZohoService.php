<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Cookie\CookieJar;

class ZohoService
{
    
    public function getAccessToken()
    {
        $clientID = config('services.zoho.client_id');
        $clientSecret = config('services.zoho.client_secret');
        $redirectUri = config('services.zoho.redirect_uri');
        $refreshToken = "1000.4f137b39002c78113a04602a1e77ab26.e8c266a2a304861e19a5b79aee2d00b9";
        $grandtype = "refresh_token";
        
        // $response= Http::post('https://accounts.zoho.com/oauth/v2/token?refresh_token='.$refreshToken.'&client_id='.$clientID.'&client_secret='.$clientSecret.'&redirect_uri='.$redirectUri.'&grant_type='.$grandtype);
        
        $response = Http::withOptions(['verify' => false])->asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'refresh_token' => $refreshToken,
            'client_id' => $clientID,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'refresh_token',
        ]);
        

        return $response->json();
    }
}