<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Cookie\CookieJar;

class ZohoService
{

    protected $clientID;
    protected $clientSecret;
    protected $redirectUri;
    protected $refreshToken;
    protected $scope;
    protected $grandtype;

    public function __construct()
    {
        
        $this->scope = config('services.zoho.scope');
        $this->clientID = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->redirectUri = config('services.zoho.redirect_uri');
        $this->refreshToken = "1000.4f137b39002c78113a04602a1e77ab26.e8c266a2a304861e19a5b79aee2d00b9";
        $this->grandtype = "refresh_token";
    }

    public function getAuthorizationCode()
    {
        
        $response = Http::withOptions(['verify' => false])->asForm()->get('https://accounts.zoho.com/oauth/v2/token', [
            'scope' => $this->scope,
            'client_id' => $this->clientID,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri,
        ]);
        return $response->json();
    }
    
    public function getAccessToken()
    {
        $response = Http::withOptions(['verify' => false])->asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientID,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'refresh_token',
        ]);
        return $response->json();
    }
}