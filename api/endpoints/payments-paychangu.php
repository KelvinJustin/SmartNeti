<?php

/**
 * POST /api/payments/paychangu/initiate - Initiate PayChangu payment
 * GET /api/payments/:id/status - Check payment status
 */

$userId = requireAuth();

// Get user info
$user = ORM::for_table('tbl_customers')->find_one($userId);
if (!$user) {
    sendJsonResponse(false, null, 'User not found', 404);
}

// Get PayChangu config
$config = ORM::for_table('tbl_appconfig')->where_like('setting', 'paychangu_%')->find_many();
$paychanguConfig = [];
foreach ($config as $c) {
    $paychanguConfig[$c['setting']] = $c['value'];
}

if (empty($paychanguConfig['paychangu_public_key']) || empty($paychanguConfig['paychangu_secret_key'])) {
    sendJsonResponse(false, null, 'PayChangu payment gateway not configured', 500);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['plan_id'])) {
        sendJsonResponse(false, null, 'Plan ID is required', 400);
    }

    $planId = $input['plan_id'];
    $routerId = isset($input['router_id']) ? $input['router_id'] : null;

    // Get plan details
    $plan = ORM::for_table('tbl_plans')->find_one($planId);
    if (!$plan) {
        sendJsonResponse(false, null, 'Plan not found', 404);
    }

    // Generate unique transaction reference
    $tx_ref = 'INV-' . time() . '-' . rand(1000, 9999);

    // Get base URL for callbacks
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

    // Create transaction record
    $trx = ORM::for_table('tbl_payment_gateway')->create();
    $trx->username = $user['username'];
    $trx->user_id = $userId;
    $trx->plan_id = $planId;
    $trx->plan_name = $plan['name_plan'];
    $trx->price = $plan['price'];
    $trx->routers_id = $routerId ?: 0;
    $trx->routers = $routerId ? ORM::for_table('tbl_routers')->find_one($routerId)['name'] : 'radius';
    $trx->gateway = 'PayChangu';
    $trx->gateway_trx_id = $tx_ref;
    $trx->payment_method = 'PayChangu';
    $trx->payment_channel = 'Mobile App';
    $trx->created_date = date('Y-m-d H:i:s');
    $trx->expired_date = date('Y-m-d H:i:s', strtotime("+30 minutes"));
    $trx->status = 1; // unpaid
    $trx->save();

    // Parse customer name
    $nameParts = explode(' ', $user['fullname']);
    $firstName = $nameParts[0] ?? '';
    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

    // Return PayChangu inline checkout parameters
    sendJsonResponse(true, [
        'payment_id' => (int)$trx->id(),
        'public_key' => $paychanguConfig['paychangu_public_key'],
        'tx_ref' => $tx_ref,
        'amount' => (float)$plan['price'],
        'currency' => $paychanguConfig['paychangu_currency'] ?? 'MWK',
        'callback_url' => $baseUrl . '/?_route=callback/paychangu',
        'return_url' => $baseUrl . '/?_route=order/view/' . $trx->id(),
        'customer' => [
            'email' => $user['email'],
            'first_name' => $firstName,
            'last_name' => $lastName
        ],
        'customization' => [
            'title' => 'SmartNeti - Payment',
            'description' => 'Payment for ' . $plan['name_plan']
        ],
        'meta' => [
            'invoice_id' => $trx->id(),
            'username' => $user['username']
        ]
    ], 'Payment initiated successfully');

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Payment status polling
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/api', '', $path);
    $segments = explode('/', trim($path, '/'));

    // Expected: /payments/:id/status
    if (count($segments) >= 3 && $segments[2] === 'status') {
        $paymentId = (int)$segments[1];

        $trx = ORM::for_table('tbl_payment_gateway')
            ->where('id', $paymentId)
            ->where('user_id', $userId)
            ->find_one();

        if (!$trx) {
            sendJsonResponse(false, null, 'Payment not found', 404);
        }

        $statusMap = [
            1 => 'unpaid',
            2 => 'paid',
            3 => 'failed',
            4 => 'canceled'
        ];

        $status = $statusMap[$trx['status']] ?? 'unknown';

        // If still unpaid, verify with PayChangu API
        if ($trx['status'] == 1 && !empty($trx['gateway_trx_id'])) {
            $verified = paychangu_verify_transaction($trx['gateway_trx_id']);
            if ($verified) {
                // Update status if verified
                $userObj = ORM::for_table('tbl_customers')
                    ->where('username', $trx['username'])
                    ->find_one();

                if ($userObj) {
                    if (!Package::rechargeUser($userObj['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
                        _log("PayChangu Payment Verification Successful, But Failed to activate Package for user: " . $userObj['username']);
                    }
                }

                $trx->payment_method = 'PayChangu';
                $trx->paid_date = date('Y-m-d H:i:s');
                $trx->status = 2;
                $trx->save();

                $status = 'paid';
            }
        }

        sendJsonResponse(true, [
            'payment_id' => (int)$trx['id'],
            'tx_ref' => $trx['gateway_trx_id'],
            'amount' => (float)$trx['price'],
            'plan_name' => $trx['plan_name'],
            'status' => $status,
            'created_date' => $trx['created_date'],
            'paid_date' => $trx['paid_date']
        ]);
    } else {
        sendJsonResponse(false, null, 'Invalid endpoint', 404);
    }

} else {
    sendJsonResponse(false, null, 'Method not allowed', 405);
}

// Helper function to verify transaction with PayChangu API
function paychangu_verify_transaction($tx_ref)
{
    global $paychanguConfig;

    $url = 'https://api.paychangu.com/verify-payment/' . $tx_ref;

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $paychanguConfig['paychangu_secret_key']
        ]
    ]);
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $responseData = json_decode($response);

    if (isset($responseData->status) && $responseData->status == 'success' && isset($responseData->data->status)) {
        return $responseData->data->status == 'success';
    }

    return false;
}
