<?php

namespace App\Services;




use App\Models\SalePerson;
use DB;
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
        $apiToken = ApiToken::find(2);
        $client = new Client();
        $response = $client->post('https://accounts.zoho.com/oauth/v2/token', [
            'verify' => false,
            'form_params' => [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientID,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
            ]
        ]);
        $data = json_decode($response->getBody(), true);
        $newAccesstoken = $data['access_token'];
        $apiToken->zoho_access_token = $newAccesstoken;
        $apiToken->save();
        return $newAccesstoken;
    }
    

    public function getAccessToken()
    {
        $apiToken = ApiToken::find(2);
        $accessToken = $apiToken->zoho_access_token;
        $client = new Client();
        try {
            $client->get('https://www.zohoapis.com/invoice/v3/contacts?organization_id=' . $this->orgId, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);
            return $accessToken;
        } catch (\GuzzleHttp\Exception\ClientException $exp) {
            $response = json_decode($exp->getResponse()->getBody(), true);
            // if ($response['code'] == 401) {
            return $this->refreshAccessToken();
            // } else if ($response['code'] == 57) {
            //     return $this->refreshAccessToken();
            // } else {
            //     return $exp;
            // }
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
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->post('https://www.zohoapis.com/invoice/v3/contacts?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'content-type' => 'application/json',
            ],
            'json' => [
                'contact_name' => $customer_name,
                'company_name' => "ABC & Co",
                'payment_terms' => 15,
                'website' => 'www.muhammadali.org',
                'status' => $status == 1 ? "Active" : "Inactive",
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
                'Content-Type' => 'application/json',
            ]
        ]);
        // $response = $client->get("https://www.zohoapis.com/invoice/v3/contacts?organization_id={$this->orgId}&page=2&per_page=200", [
        //     'verify' => false,
        //     'headers' => [
        //         'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
        //         'Content-Type' => 'application/json',
        //     ]
        // ]);


        $data = json_decode($response->getBody(), true);
        return $data['contacts'] ?? [];
    }

    public function getAllSalespersons()
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/invoice/v3/salespersons?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['data'] ?? [];
    }


    public function getCustomerDetail($customerId)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/invoice/v3/contacts/' . $customerId . '?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function updateCustomer($id, $customer_name, $status, $contact_person, $billing_address)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $customerId = Customer::select('zoho_customer_id')->where('id', $id)->first();
        $response = $client->put('https://www.zohoapis.com/invoice/v3/contacts/' . $customerId->zoho_customer_id, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contact_name' => $customer_name,
                'company_name' => "ABC & Co",
                'payment_terms' => 15,
                'website' => 'www.muhammadali.org',
                'status' => $status == 1 ? "Active" : "Inactive",
                'contact_persons' => $contact_person,
                'billing_address' => $billing_address,
            ]
        ]);
    }

    public function deleteCustomer($id)
    {
        $accessToken = $this->getAccessToken();
        $customerId = Customer::select('zoho_customer_id')->where('id', $id)->first();
        $client = new Client();
        $response = $client->delete('https://www.zohoapis.com/invoice/v3/contacts/' . $customerId->zoho_customer_id, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    // Invoice Status Manage Functions Zoho
    public function markAsDraft($invoiceID)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->post('https://www.zohoapis.com/invoice/v3/invoices/' . $invoiceID . '/status/draft', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function markAsSent($invoiceID)
    {
        $accessToken = $this->getAccessToken();
        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://www.zohoapis.com/invoice/v3/invoices/' . $invoiceID . '/status/sent', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'Content-Type' => 'application/json'
            ],
        ]);

        $result = json_decode($response->getBody(), true);
        \Log::info('Zoho markAsSent response:', $result);
        return $result;
    }


    public function recordPayment($customerId, $invoiceId, $amount, $paymentDate)
    {
        $paymentDate = $paymentDate ?? date('Y-m-d'); 
        $accessToken = $this->getAccessToken();

        $client = new \GuzzleHttp\Client();

        $body = [
            "customer_id" => $customerId,
            "amount" => $amount,
            "date" => $paymentDate,
            "invoices" => [
                [
                    "invoice_id" => $invoiceId,
                    "amount_applied" => $amount
                ]
            ]
        ];
        $response = $client->post('https://www.zohoapis.com/invoice/v3/customerpayments', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($body)
        ]);

        return json_decode($response->getBody(), true);
    }


    // Invoice Manage Functions Zoho
    public function createInvoice($customerId, $notes, $currency_code, $lineitems, $salesPersonId, $salesPersonName, $code)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $customer = Customer::select('zoho_customer_id')->where('id', $customerId)->first();
        $saleperson = SalePerson::select('zoho_salesperson_id')->where('id', $salesPersonId)->first();
        // $code = $this->generateUniqueCode('invoices', 'zoho_invoice_number');

        $response = $client->post('https://www.zohoapis.com/invoice/v3/invoices?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'customer_id' => $customer->zoho_customer_id,
                'salesperson_id' => $saleperson->zoho_salesperson_id,
                'invoice_number' => $code,
                'notes' => $notes,
                'currency_code' => $currency_code,
                'line_items' => $lineitems,
                "custom_fields" => [
                    [
                        "api_name" => "cf_sales_person",
                        "value" => $salesPersonName
                    ]
                ]

            ]
        ]);


        // $payload = [
        //     'customer_id' => $customer->zoho_customer_id,
        //     'invoice_number' => $code,
        //     'notes' => $notes,
        //     'currency_code' => $currency_code,
        //     'line_items' => $lineitems,
        //     "custom_fields" => [
        //         [
        //             "api_name" => "cf_sales_person",
        //             "value" => $salesPersonName
        //         ]
        //     ]
        // ];

        // if ($saleperson && !empty($saleperson->zoho_salesperson_id)) {
        //     $payload['salesperson_id'] = $saleperson->zoho_salesperson_id;
        // }
        // $response = $client->post('https://www.zohoapis.com/invoice/v3/invoices?organization_id=' . $this->orgId, [
        //     'verify' => false,
        //     'headers' => [
        //         'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => $payload
        // ]);

        return json_decode($response->getBody(), true);
    }
    public function getZohoInvoice($invoiceId)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();

        $response = $client->get('https://www.zohoapis.com/invoice/v3/invoices/' . $invoiceId . '?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function createZohoCreditNote($customerId, $invoiceId, $notes, $currency_code, $place_of_supply, $lineItems, $creditNoteNumber, $refundDate)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();

        // Fetch Zoho customer ID
        $customer = Customer::select('zoho_customer_id')->where('id', $customerId)->first();
        if (!$customer) {
            throw new \Exception('Customer not found.');
        }
        $zohoInvoice = $this->getZohoInvoice($invoiceId);
        $placeOfSupply = $zohoInvoice['invoice']['place_of_supply'] ?? null;

        // dd($placeOfSupply);
        $payload = [
            'customer_id' => $customer->zoho_customer_id,
            'invoice_id' => $invoiceId,
            'creditnote_number' => $creditNoteNumber,
            'date' => $refundDate,
            'notes' => $notes,
            'currency_code' => $currency_code,
            'place_of_supply' => $placeOfSupply,
            'line_items' => $lineItems,
        ];

        $response = $client->post('https://www.zohoapis.com/invoice/v3/creditnotes?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getInvoice($id)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/invoice/v3/invoices/' . $id, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getInvoiceD($id)
    {
        try {
            $accessToken = $this->getAccessToken();

            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://www.zohoapis.com/invoice/v3/invoices/' . $id, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'X-com-zoho-invoice-organizationid' => $this->orgId,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateInvoice($invoiceID, $json)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->put('https://www.zohoapis.com/invoice/v3/invoices/' . $invoiceID, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'content-type' => 'application/json',
            ],
            'json' => $json
        ]);
        return json_decode($response->getBody(), true);
    }

    public function deleteInvoice($invoiceID)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->delete('https://www.zohoapis.com/invoice/v3/invoices/' . $invoiceID, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }

    // Tax Manage Functions Zoho
    public function taxList()
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/invoice/v3/settings/taxes', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-invoice-organizationid' => $this->orgId
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function taxCreate($tax)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->post('https://www.zohoapis.com/invoice/v3/settings/taxes', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . '1000.49614085541dc3de8f0ff163b97d5fd9.ebd8422fc81c91e9ec459f247ebba4d1',
                'X-com-zoho-invoice-organizationid' => $this->orgId,
                'content-type' => 'application/json',
            ],
            'json' => $tax
        ]);

        return json_decode($response->getBody(), true);
    }


    public static function generateUniqueCode($table, $field)
    {
        // return '5000';

        $maxPos = DB::table($table)->where('status', 1)->whereNull('deleted_at')->max($field);

        $maxPos = $maxPos + 1;
        // dd($maxPos);
        return $maxPos;
    }

}
