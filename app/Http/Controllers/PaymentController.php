<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function makePayment(Request $request)
    {
        $apiKey = env('PAYMOB_API_KEY');

        //  Step 1: Authentication
        $auth = Http::post(env('PAYMOB_BASE_URL') . '/auth/tokens', [
            'api_key' => $apiKey
        ])->json();

        $token = $auth['token'];

        //  Step 2: Create Order
        $order = Http::post(env('PAYMOB_BASE_URL') . '/ecommerce/orders', [
            'auth_token' => $token,
            'delivery_needed' => false,
            'amount_cents' => $request->amount * 100,
            'currency' => 'EGP',
            'items' => [],
        ])->json();

        // Step 3: Payment Key
        $paymentKey = Http::post(env('PAYMOB_BASE_URL') . '/acceptance/payment_keys', [
            'auth_token' => $token,
            'amount_cents' => $request->amount * 100,
            'expiration' => 3600,
            'order_id' => $order['id'],
            'billing_data' => [
                "apartment" => "NA",
                "email" => $request->email,
                "floor" => "NA",
                "first_name" => $request->name,
                "street" => "NA",
                "building" => "NA",
                "phone_number" => "+201000000000",
                "shipping_method" => "NA",
                "postal_code" => "NA",
                "city" => "Cairo",
                "country" => "EG",
                "last_name" => "Customer",
                "state" => "Cairo",
            ],
            'currency' => 'EGP',
            'integration_id' => env('PAYMOB_IFRAME_ID'),
        ])->json();

        //  Step 4: Redirect to Paymob iframe
        $iframeUrl = "https://accept.paymob.com/api/acceptance/iframes/" . env('PAYMOB_IFRAME_ID') . "?payment_token=" . $paymentKey['token'];

        return redirect($iframeUrl);
    }
}
