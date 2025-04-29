<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Services\ZohoInvoice;
use Illuminate\Support\Facades\Http;

class ZohoController extends Controller
{
    protected $zohoinvoice;

    public function __construct(ZohoInvoice $zohoinvoice)
    {
        $this->zohoinvoice = $zohoinvoice;
    }

    public function getAccessToken()
    {
        return $this->zohoinvoice->getAccessToken();
    }

    public function createInvoice()
    {
        return $this->zohoinvoice->createInvoice();
    }

}
