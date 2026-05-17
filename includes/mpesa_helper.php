<?php
require_once 'config.php';

/**
 * Format phone number to 2547XXXXXXXX
 */
function formatMpesaPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strpos($phone, '0') === 0) {
        $phone = '254' . substr($phone, 1);
    } elseif (strpos($phone, '254') !== 0) {
        // If it doesn't start with 0 and doesn't start with 254, assume it's just the 9 digits
        if (strlen($phone) == 9) {
            $phone = '254' . $phone;
        }
    }
    return $phone;
}

/**
 * Generate M-Pesa OAuth Token
 */
function generateMpesaToken() {
    $url = (MPESA_ENV == 'sandbox') 
        ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' 
        : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("cURL Error: " . $error);
    }

    $result = json_decode($response);
    
    if (isset($result->access_token)) {
        return $result->access_token;
    }
    
    throw new Exception("Failed to generate token: " . $response);
}

/**
 * Initiate M-Pesa STK Push
 */
function initiateStkPush($phone, $amount, $reference, $description) {
    $token = generateMpesaToken();
    $url = (MPESA_ENV == 'sandbox')
        ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    // CallBack URL must be accessible from the internet. 
    $callbackUrl = rtrim(SITE_URL, '/') . '/api/mpesa_callback.php';
    
    // Safaricom rejects 'localhost'. Use a dummy public URL if running locally just to test STK Push delivery.
    if (strpos($callbackUrl, 'localhost') !== false || strpos($callbackUrl, '127.0.0.1') !== false) {
        $callbackUrl = 'https://mydomain.com/loan-system/api/mpesa_callback.php';
    }

    $curl_post_data = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => MPESA_SHORTCODE,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => $reference,
        'TransactionDesc' => $description
    ];

    $data_string = json_encode($curl_post_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("cURL Error: " . $error);
    }

    $result = json_decode($response, true);
    return $result;
}
?>
