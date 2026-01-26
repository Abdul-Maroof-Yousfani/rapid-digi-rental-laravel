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
    
    public function getAccessToken($retryCount = 0, $maxRetries = 3)
    {
        try {
            $response = Http::withOptions(['verify' => false])->asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientID,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->redirectUri,
                'grant_type' => 'refresh_token',
            ]);
            
            $data = $response->json();
            
            // Check for rate limiting error
            if ($response->status() === 400 && isset($data['error'])) {
                $errorDescription = $data['error_description'] ?? '';
                
                // Check if it's a rate limit error
                if (stripos($errorDescription, 'too many requests') !== false || 
                    stripos($errorDescription, 'try again after') !== false) {
                    
                    \Log::warning("Zoho Rate Limit Error (ZohoService)", [
                        'error' => $data['error'],
                        'description' => $errorDescription,
                        'retry_count' => $retryCount
                    ]);
                    
                    // Retry with exponential backoff
                    if ($retryCount < $maxRetries) {
                        $waitTime = pow(2, $retryCount) * 2; // 2, 4, 8 seconds
                        \Log::info("Retrying Zoho token refresh after {$waitTime} seconds", [
                            'retry_count' => $retryCount + 1
                        ]);
                        
                        sleep($waitTime);
                        return $this->getAccessToken($retryCount + 1, $maxRetries);
                    } else {
                        \Log::error("Zoho Rate Limit: Max retries exceeded (ZohoService)", [
                            'max_retries' => $maxRetries
                        ]);
                        return [
                            'error' => 'Access Denied',
                            'error_description' => 'Zoho API rate limit exceeded. Please try again later.'
                        ];
                    }
                }
            }
            
            return $data;
            
        } catch (\Exception $e) {
            \Log::error("Zoho Service Exception", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'error' => 'error',
                'error_description' => $e->getMessage()
            ];
        }
    }
}