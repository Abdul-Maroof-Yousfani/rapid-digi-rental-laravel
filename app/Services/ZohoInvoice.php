<?php

namespace App\Services;




use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Cookie\CookieJar;
use App\Models\ApiToken;
use App\Models\Customer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ZohoInvoice
{

    protected $clientID;
    protected $clientSecret;
    protected $redirectUri;
    protected $refreshToken;
    protected $scope;
    protected $orgId;

    public function __construct()
    {
        $this->scope = config('services.zoho.scope');
        $this->clientID = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->redirectUri = config('services.zoho.redirect_uri');
        $this->orgId = config('services.zoho.org_id');
        $this->refreshToken = config('services.zoho.refresh_token');
    }

    public function refreshAccessToken()
    {
        $apiToken= ApiToken::find(2);
        $client= new Client();
        $response= $client->post('https://accounts.zoho.com/oauth/v2/token', [
            'verify' => false,
            'form_params' => [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientID,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
            ]
        ]);
        $data= json_decode($response->getBody(), true);
        $newAccesstoken= $data['access_token'];
        $apiToken->zoho_access_token= $newAccesstoken;
        $apiToken->save();
        return $newAccesstoken;
    }

    public function getAccessToken()
    {
        $apiToken= ApiToken::find(2);
        $accessToken= $apiToken->zoho_access_token;
        $client= new Client();
        try {
            $client->get('https://www.zohoapis.com/invoice/v3/contacts?organization_id=' . $this->orgId, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
            ]);
            return $accessToken;
        } catch (\GuzzleHttp\Exception\ClientException $exp) {
            $response= json_decode($exp->getResponse()->getBody(), true);
            if($response['code'] == 57){ return $this->refreshAccessToken();}
            else { return $exp; }
        }
    }

    // Customer Manage Functions Zoho
    public function searchCustomer($email, $phone)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $searchText = $email ? $email : $phone;
        $response = $client->get("https://www.zohoapis.com/invoice/v3/contacts?organization_id={$this->orgId}&search_text={$searchText}", [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        if (!empty($data['contacts'])) {
            return $data['contacts'][0];
        }
        return null;
    }

    public function createCustomer($customer_name, $status, $contact_person, $billing_address)
    {
        $accessToken= $this->getAccessToken();
        $client = new Client();
        $response= $client->post('https://www.zohoapis.com/invoice/v3/contacts?organization_id='. $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '.$accessToken,
                'content-type' => 'application/json',
            ],
            'json' => [
                'contact_name' => $customer_name,
                'company_name' => "ABC & Co",
                'payment_terms'=> 15,
                'website'=> 'www.muhammadali.org',
                'status' => $status==1 ? "Active" : "Inactive",
                'contact_persons' => $contact_person,
                'billing_address' => $billing_address,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getAllCustomers()
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/invoice/v3/contacts?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type'  => 'application/json',
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['contacts'] ?? [];
    }

    public function getCustomerDetail($customerId)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->get('https://www.zohoapis.com/invoice/v3/contacts/'.$customerId.'?organization_id='. $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function updateCustomer($id, $customer_name, $status, $contact_person, $billing_address)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $customerId= Customer::select('zoho_customer_id')->where('id', $id)->first();
        $response= $client->put('https://www.zohoapis.com/invoice/v3/contacts/'.$customerId->zoho_customer_id,[
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contact_name' => $customer_name,
                'company_name' => "ABC & Co",
                'payment_terms'=> 15,
                'website'=> 'www.muhammadali.org',
                'status' => $status==1 ? "Active" : "Inactive",
                'contact_persons' => $contact_person,
                'billing_address' => $billing_address,
            ]
        ]);
    }

    public function deleteCustomer($id)
    {
        $accessToken= $this->getAccessToken();
        $customerId= Customer::select('zoho_customer_id')->where('id', $id)->first();
        $client= new Client();
        $response= $client->delete('https://www.zohoapis.com/invoice/v3/contacts/'.$customerId->zoho_customer_id, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    // Invoice Status Manage Functions Zoho
    public function markAsDraft($invoiceID)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->post('https://www.zohoapis.com/invoice/v3/invoices/'.$invoiceID.'/status/draft', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function markAsSent($invoiceID)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->post('https://www.zohoapis.com/invoice/v3/invoices/'.$invoiceID.'/status/sent', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    // Invoice Manage Functions Zoho
    public function createInvoice($customerId, $notes, $currency_code, $lineitems)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $customer= Customer::select('zoho_customer_id')->where('id', $customerId)->first();
        $response = $client->post('https://www.zohoapis.com/invoice/v3/invoices?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
            'Authorization' => 'Zoho-oauthtoken ' .$accessToken,
            'Content-Type'  => 'application/json',
            ],
            'json' => [
                'customer_id' => $customer->zoho_customer_id,
                'notes' => $notes,
                'currency_code' => $currency_code,
                'line_items' => $lineitems
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getInvoice($id)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->get('https://www.zohoapis.com/invoice/v3/invoices/'.$id, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '.$accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function updateInvoice($invoiceID,$json)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->put('https://www.zohoapis.com/invoice/v3/invoices/'.$invoiceID, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'content-type' => 'application/json',
            ],
            'json' => $json
        ]);
        return json_decode($response->getBody(), true);
    }

    public function deleteInvoice($invoiceID)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->delete('https://www.zohoapis.com/invoice/v3/invoices/'.$invoiceID, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    // Tax Manage Functions Zoho
    public function taxList()
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->get('https://www.zohoapis.com/invoice/v3/settings/taxes', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '. $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function taxCreate($tax)
    {
        $accessToken= $this->getAccessToken();
        $client= new Client();
        $response= $client->post('https://www.zohoapis.com/invoice/v3/settings/taxes',[
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken '.'1000.49614085541dc3de8f0ff163b97d5fd9.ebd8422fc81c91e9ec459f247ebba4d1',
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'content-type' => 'application/json',
            ],
            'json' => $tax
        ]);

        return json_decode($response->getBody(), true);
    }







// public function index(){
    //     $url = 'https://accounts.zoho.com/oauth/v2/auth?' . http_build_query([
    //         'scope'         => $this->scope,
    //         'client_id'     => $this->clientID,
    //         'state'         => 'testing',
    //         'response_type' => 'code',
    //         'redirect_uri'  => $this->redirectUri,
    //         'access_type'   => 'offline',
    //     ]);

    //     return redirect()->away($url);

    // }

    // public function redirectToZoho(Request $request)
    // {
    //     $code= $request->code;
    //     return view('Api.zohoapi', compact('code'));
    // }

    // public function getRefreshAndAccessToken(Request $request)
    // {
    //     $code= $request->code;
    //     $response = Http::withOptions(['verify' => false])->asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
    //         'code' => $code,
    //         'client_id' => '1000.GN0KHG7RG4BNRL3B3OT9C2U2K62R7V',
    //         'client_secret' => '00c794da6681c7bbd82fd6be748dabb218450349a1',
    //         'redirect_uri' => 'http://localhost:8000/zoho/callback',
    //         'grant_type' => 'authorization_code',
    //     ]);

    //     $data = $response->json();

    //     if (isset($data['refresh_token'])) {
    //         ApiToken::updateOrCreate(
    //             ['zoho_refresh_token' => $data['refresh_token']],
    //         );
    //     }

    //     return response()->json($data);
    //     // dd($response->json());
    // }

    // $response = Http::withOptions(['verify' => false])->asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
    //     'refresh_token' => $this->refreshToken,
    //     'client_id' => $this->clientID,
    //     'client_secret' => $this->clientSecret,
    //     'redirect_uri' => $this->redirectUri,
    //     'grant_type' => 'refresh_token',
    //     ]);
// return $response->json();

}
