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
        $this->clientID = '1000.IP19P9N5DEG6Q105YH00QNDFJFDIEI';
        $this->clientSecret = '0fcc1a0957e78eb16a9b28dae52ac92cd4fd5a3bfc';
        $this->redirectUri = config('services.zoho.redirect_uri');
        $this->orgId = '869372301';
        $this->refreshToken = '1000.4689d534dcb2476cf5f2e49ea139f761.cbd43fab74d5e6bbbc8bee90db79c89a';
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
        //   if (!isset($data['access_token'])) {
        //     \Log::error("Zoho Refresh Token Error", [
        //         'response' => $data
        //     ]);

        //     return response()->view('sitedown-error', [], 500);
        // }
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
            $client->get('https://www.zohoapis.com/billing/v1/contacts?organization_id=' . $this->orgId, [
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
        $response = $client->get("https://www.zohoapis.com/billing/v1/contacts?organization_id={$this->orgId}&search_text={$searchText}", [
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
        $response = $client->post('https://www.zohoapis.com/billing/v1/contacts?organization_id=' . $this->orgId, [
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

    $response = $client->get(
        'https://www.zohoapis.com/billing/v1/customers',
        [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
                'X-com-zoho-subscriptions-organizationid' => $this->orgId,
            ]
        ]
    );

    $data = json_decode($response->getBody(), true);

    return $data['customers'] ?? [];
}


    public function getAllSalespersons()
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/billing/v1/salespersons?organization_id=' . $this->orgId, [
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
        $response = $client->get('https://www.zohoapis.com/billing/v1/contacts/' . $customerId . '?organization_id=' . $this->orgId, [
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
        $response = $client->put('https://www.zohoapis.com/billing/v1/contacts/' . $customerId->zoho_customer_id, [
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
        $response = $client->delete('https://www.zohoapis.com/billing/v1/contacts/' . $customerId->zoho_customer_id, [
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
        $response = $client->post('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceID . '/status/draft', [
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

        $response = $client->post('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceID . '/sent', [
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


    public function applyToInvoice($creditNoteId, $invoiceId, $amount)
    {
        $accessToken = $this->getAccessToken();
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            'https://www.zohoapis.com/creditnotes/v3/creditnotes/' . $creditNoteId . '/invoices',
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'X-com-zoho-invoice-organizationid' => $this->orgId,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'invoices' => [
                        [
                            'invoice_id' => $invoiceId,
                            'amount_applied' => $amount,
                        ]
                    ]
                ],
            ]
        );

        $result = json_decode($response->getBody(), true);
        // dd($result);
        \Log::info('Zoho Credit Note Applied:', $result);

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
        $response = $client->post('https://www.zohoapis.com/billing/v1/customerpayments', [
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
    public function createInvoice(
        $customerId,
        $notes,
        $currency_code,
        $lineitems,
        $salesPersonId,
        $salesPersonName,
        $code
    ) {
        $accessToken = $this->getAccessToken();
        $client = new Client();

        $customer = Customer::select('zoho_customer_id')->where('id', $customerId)->first();
        $saleperson = SalePerson::select('zoho_salesperson_id')->where('id', $salesPersonId)->first();

        // Convert your line items to billing format
        $invoiceItems = [];
        foreach ($lineitems as $item) {
            $invoiceItems[] = [
                "product_id" => $item['product_id'] ?? null,   // required if you have product id
                "name" => $item['name'],
                "description" => $item['description'],
                "price" => (float) $item['rate'],
                "quantity" => (int) $item['quantity'],
                "discount" => (float) $item['discount'],
                "tax_id" => $item['tax_id'] ?? null,
            ];
        }

        $payload = [
            "customer_id" => $customer->zoho_customer_id,
            "invoice_number" => $code,
            "reference_number" => $code,
            "currency_code" => $currency_code,
            "discount_type" => "item_level",
            "salesperson_name" => $salesPersonName,
            "notes" => $notes,
            "invoice_items" => $invoiceItems,
            "custom_fields" => [
                [
                    "label" => "Sales Person",
                    "value" => $salesPersonName
                ]
            ]
        ];

        $response = $client->post(
            'https://www.zohoapis.com/billing/v1/invoices?organization_id=' . $this->orgId,
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]
        );

        return json_decode($response->getBody(), true);
    }

    public function getZohoInvoice($invoiceId)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();

        $response = $client->get('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceId . '?organization_id=' . $this->orgId, [
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

        $response = $client->post('https://www.zohoapis.com/billing/v1/creditnotes?organization_id=' . $this->orgId, [
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
        $response = $client->get('https://www.zohoapis.com/billing/v1/invoices/' . $id, [
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
            $response = $client->get('https://www.zohoapis.com/billing/v1/invoices/' . $id, [
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
        $response = $client->put('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceID, [
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
        $response = $client->delete('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceID, [
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

        $response = $client->get(
            'https://www.zohoapis.com/billing/v1/settings/taxes?organization_id=' . $this->orgId,
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }


    public function taxCreate($tax)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();

        $response = $client->post(
            'https://www.zohoapis.com/billing/v1/settings/taxes?organization_id=' . $this->orgId,
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $tax
            ]
        );

        return json_decode($response->getBody(), true);
    }



    public static function generateUniqueCode($table, $field)
    {
        // return '7001';

        $maxPos = DB::table($table)->where('status', 1)->whereNull('deleted_at')->max($field);

        $maxPos = $maxPos + 1;
        // dd($maxPos);
        return $maxPos;
    }

}
