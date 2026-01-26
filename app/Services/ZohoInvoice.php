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
        $this->refreshToken = '1000.871cfa30c4f7783caefe2820e096a3d2.3c202174c17284f756ad07917b2b0a4c';
    }

    public function refreshAccessToken($retryCount = 0, $maxRetries = 3)
    {
        $apiToken = ApiToken::find(2);
        $client = new Client();
        
        try {
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
            
            // Check for rate limiting error (can be "Access Denied" or other error types)
            if (isset($data['error'])) {
                $errorDescription = $data['error_description'] ?? '';
                
                // Check if it's a rate limit error
                if (stripos($errorDescription, 'too many requests') !== false || 
                    stripos($errorDescription, 'try again after') !== false ||
                    stripos($errorDescription, 'too many requests continuously') !== false) {
                    
                    \Log::warning("Zoho Rate Limit Error", [
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
                        return $this->refreshAccessToken($retryCount + 1, $maxRetries);
                    } else {
                        \Log::error("Zoho Rate Limit: Max retries exceeded", [
                            'max_retries' => $maxRetries
                        ]);
                        throw new \Exception("Zoho API rate limit exceeded. Please try again later.");
                    }
                }
            }
            
            // Check if access token is present
            if (!isset($data['access_token'])) {
                \Log::error("Zoho Refresh Token Error", [
                    'response' => $data
                ]);
                throw new \Exception("Failed to refresh Zoho access token: " . ($data['error_description'] ?? 'Unknown error'));
            }
            
            $newAccesstoken = $data['access_token'];
            $apiToken->zoho_access_token = $newAccesstoken;
            $apiToken->save();
            
            // Cache the new token for 50 minutes (tokens typically expire in 1 hour)
            $cacheKey = 'zoho_access_token_' . $this->orgId;
            Cache::put($cacheKey, $newAccesstoken, now()->addMinutes(50));
            
            return $newAccesstoken;
            
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
            
            // Handle rate limiting (400 Bad Request with specific error)
            if ($statusCode === 400 && isset($responseBody['error'])) {
                $errorDescription = $responseBody['error_description'] ?? '';
                
                if (stripos($errorDescription, 'too many requests') !== false || 
                    stripos($errorDescription, 'try again after') !== false) {
                    
                    \Log::warning("Zoho Rate Limit Error (ClientException)", [
                        'error' => $responseBody['error'],
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
                        return $this->refreshAccessToken($retryCount + 1, $maxRetries);
                    } else {
                        \Log::error("Zoho Rate Limit: Max retries exceeded", [
                            'max_retries' => $maxRetries
                        ]);
                        throw new \Exception("Zoho API rate limit exceeded. Please try again later.");
                    }
                }
            }
            
            \Log::error("Zoho Refresh Token ClientException", [
                'status_code' => $statusCode,
                'response' => $responseBody,
                'message' => $e->getMessage()
            ]);
            
            throw new \Exception("Failed to refresh Zoho access token: " . ($responseBody['error_description'] ?? $e->getMessage()));
            
        } catch (\Exception $e) {
            \Log::error("Zoho Refresh Token Exception", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }


    public function getAccessToken()
    {
        // Check cache first to avoid unnecessary API calls
        $cacheKey = 'zoho_access_token_' . $this->orgId;
        $cachedToken = Cache::get($cacheKey);
        
        if ($cachedToken) {
            return $cachedToken;
        }
        
        $apiToken = ApiToken::find(2);
        $accessToken = $apiToken->zoho_access_token;
        $client = new Client();
        
        try {
            $client->get('https://www.zohoapis.com/billing/v1/customers?organization_id=' . $this->orgId, [
                'verify' => false,
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);
            
            // Cache the token for 50 minutes (tokens typically expire in 1 hour)
            Cache::put($cacheKey, $accessToken, now()->addMinutes(50));
            
            return $accessToken;
        } catch (\GuzzleHttp\Exception\ClientException $exp) {
            $response = json_decode($exp->getResponse()->getBody(), true);
            $statusCode = $exp->getResponse()->getStatusCode();
            
            // Refresh token on 401 (Unauthorized) or 403 (Forbidden)
            if ($statusCode == 401 || $statusCode == 403) {
                $newToken = $this->refreshAccessToken();
                // Cache the new token
                Cache::put($cacheKey, $newToken, now()->addMinutes(50));
                return $newToken;
            }
            
            \Log::error("Zoho API Error in getAccessToken", [
                'status_code' => $statusCode,
                'response' => $response
            ]);
            
            throw $exp;
        }
    }

    // Customer Manage Functions Zoho
public function searchCustomer($email = null, $phone = null)
{
    $accessToken = $this->getAccessToken();
    $client = new Client();

    $params = [];
    if ($email) {
        $params['email'] = $email;
    } elseif ($phone) {
        $params['phone'] = $phone;
    }

    $response = $client->get(
        'https://www.zohoapis.com/billing/v1/customers',
        [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-subscriptions-organizationid' => $this->orgId,
            ],
            'query' => $params,
        ]
    );

    $data = json_decode($response->getBody(), true);

    return $data['customers'][0] ?? null;
}


    public function createCustomer($data)
    {
        $accessToken = $this->getAccessToken();

        $client = new Client();
        $response = $client->post(
            'https://www.zohoapis.com/billing/v1/customers',
            [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                    'X-com-zoho-subscriptions-organizationid' => $this->orgId,
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]
        );

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
        
        // List of endpoints to try
        $endpoints = [
            'https://www.zohoapis.com/billing/v1/salespersons?organization_id=' . $this->orgId,
            'https://www.zohoapis.com/billing/v1/settings/salespersons?organization_id=' . $this->orgId,
            'https://www.zohoapis.com/billing/v1/settings/salespersons',
        ];
        
        $lastError = null;
        
        foreach ($endpoints as $endpoint) {
            try {
                $response = $client->get($endpoint, [
                    'verify' => false,
                    'headers' => [
                        'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                        'Content-Type' => 'application/json',
                        'X-com-zoho-subscriptions-organizationid' => $this->orgId,
                    ]
                ]);

                $data = json_decode($response->getBody(), true);
                
                // Check if response has salespersons data
                if (isset($data['salespersons']) && is_array($data['salespersons'])) {
                    \Log::info("Zoho getAllSalespersons Success", ['endpoint' => $endpoint, 'count' => count($data['salespersons'])]);
                    return $data['salespersons'];
                }
                
                if (isset($data['data']) && is_array($data['data'])) {
                    \Log::info("Zoho getAllSalespersons Success", ['endpoint' => $endpoint, 'count' => count($data['data'])]);
                    return $data['data'];
                }
                
                // If we get a successful response but no data, log it
                if ($response->getStatusCode() == 200) {
                    \Log::warning("Zoho getAllSalespersons: Success but no salespersons data", [
                        'endpoint' => $endpoint,
                        'response' => $data
                    ]);
                    return [];
                }
                
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $statusCode = $e->getResponse()->getStatusCode();
                $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                
                $lastError = [
                    'status_code' => $statusCode,
                    'message' => $responseBody['message'] ?? $e->getMessage(),
                    'code' => $responseBody['code'] ?? null,
                    'endpoint' => $endpoint
                ];
                
                \Log::warning("Zoho getAllSalespersons Endpoint Failed", $lastError);
                
                // If it's a 401/403, continue to next endpoint
                // If it's 404, the endpoint doesn't exist, try next
                if ($statusCode == 404) {
                    continue;
                }
                
                // For 401/403, try next endpoint
                if ($statusCode == 401 || $statusCode == 403) {
                    continue;
                }
                
                // For other errors, break and throw
                break;
            } catch (\Exception $e) {
                $lastError = [
                    'message' => $e->getMessage(),
                    'endpoint' => $endpoint
                ];
                \Log::error("Zoho getAllSalespersons Exception", $lastError);
                continue;
            }
        }
        
        // If all endpoints failed, log and return empty array with warning
        \Log::error("Zoho getAllSalespersons: All endpoints failed", [
            'last_error' => $lastError,
            'org_id' => $this->orgId,
            'note' => 'The salespersons endpoint may not be available or requires different OAuth scopes. ' .
                     'Check Zoho Billing API documentation or verify OAuth scope includes: ZohoSubscriptions.settings.READ'
        ]);
        
        // Return empty array instead of throwing exception to prevent breaking the sync process
        // The calling code can check if array is empty
        return [];
    }


    public function getCustomerDetail($customerId)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/billing/v1/customers/' . $customerId . '?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

public function updateCustomer($zohoCustomerId, array $data)
{
    $accessToken = $this->getAccessToken();
    $client = new Client();

    $response = $client->put(
        'https://www.zohoapis.com/billing/v1/customers/' . $zohoCustomerId,
        [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-subscriptions-organizationid' => $this->orgId,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]
    );

    return json_decode($response->getBody(), true);
}


    public function deleteCustomer($id)
    {
        $accessToken = $this->getAccessToken();
        $customerId = Customer::select('zoho_customer_id')->where('id', $id)->first();
        $client = new Client();
        $response = $client->delete('https://www.zohoapis.com/billing/v1/customers/' . $customerId->zoho_customer_id, [
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

        // First verify the invoice exists
        try {
            $invoiceCheck = $this->getZohoInvoice($invoiceID);
            if (!isset($invoiceCheck['invoice'])) {
                \Log::error('Zoho markAsSent: Invoice not found', ['invoice_id' => $invoiceID]);
                return [
                    'code' => 1002,
                    'message' => 'Invoice does not exist or cannot be accessed.'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Zoho markAsSent: Error checking invoice', [
                'invoice_id' => $invoiceID,
                'error' => $e->getMessage()
            ]);
            return [
                'code' => 1002,
                'message' => 'Invoice does not exist or cannot be accessed.'
            ];
        }

        // Use query parameter for organization_id like getZohoInvoice does
        $response = $client->post('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceID . '/converttoopen?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
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
        // Use correct endpoint: /payments (not /customerpayments) and query parameter for organization_id
        $response = $client->post('https://www.zohoapis.com/billing/v1/payments?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $body
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
        $placeOfSupply = $zohoInvoice['invoice']['place_of_supply'] ?? $place_of_supply;

        // Format line items to match Zoho API requirements (creditnote_items format)
        $creditnoteItems = [];
        foreach ($lineItems as $item) {
            // Description is mandatory - ensure it's never empty
            $description = $item['description'] ?? $item['name'] ?? 'Credit Note Item';
            
            // Use 'price' instead of 'rate' to match Zoho API format
            $price = isset($item['price']) ? (float) $item['price'] : (isset($item['rate']) ? (float) $item['rate'] : 0);
            
            $creditnoteItem = [
                'description' => $description,
                'quantity' => isset($item['quantity']) ? (int) $item['quantity'] : 1,
                'price' => $price,
            ];
            
            // Add optional fields if present
            if (isset($item['code']) && $item['code']) {
                $creditnoteItem['code'] = $item['code'];
            }
            
            if (isset($item['account_id']) && $item['account_id']) {
                $creditnoteItem['account_id'] = $item['account_id'];
            }
            
            if (isset($item['tax_id']) && $item['tax_id']) {
                $creditnoteItem['tax_id'] = $item['tax_id'];
            }
            
            if (isset($item['item_id']) && $item['item_id']) {
                $creditnoteItem['item_id'] = $item['item_id'];
            }
            
            $creditnoteItems[] = $creditnoteItem;
        }

        // Validate that we have at least one item with description
        if (empty($creditnoteItems)) {
            throw new \Exception('At least one credit note item with description is required.');
        }

        $payload = [
            'customer_id' => $customer->zoho_customer_id,
            'invoice_id' => $invoiceId,
            'date' => $refundDate,
            'notes' => $notes ?? '',
            'currency_code' => $currency_code,
            'creditnote_items' => $creditnoteItems,
        ];

        // Add optional fields
        if ($placeOfSupply) {
            $payload['place_of_supply'] = $placeOfSupply;
        }
        
        if ($creditNoteNumber) {
            $payload['creditnote_number'] = $creditNoteNumber;
        }

        \Log::info('Creating Zoho credit note', [
            'customer_id' => $customer->zoho_customer_id,
            'invoice_id' => $invoiceId,
            'items_count' => count($creditnoteItems),
        ]);

        // Use header instead of query parameter as per API documentation
        $response = $client->post('https://www.zohoapis.com/billing/v1/creditnotes', [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'X-com-zoho-subscriptions-organizationid' => $this->orgId,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $result = json_decode($response->getBody(), true);
        
        \Log::info('Zoho credit note creation response', ['response' => $result]);
        
        return $result;
    }

    public function getInvoice($id)
    {
        $accessToken = $this->getAccessToken();
        $client = new Client();
        $response = $client->get('https://www.zohoapis.com/billing/v1/invoices/' . $id . '?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
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
        $response = $client->put('https://www.zohoapis.com/billing/v1/invoices/' . $invoiceID . '?organization_id=' . $this->orgId, [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                'Content-Type' => 'application/json',
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
