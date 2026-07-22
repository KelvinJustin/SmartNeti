<?php

/**
 * PHP Mikrotik Billing (https://github.com/hotspotbilling/phpnuxbill/)
 *
 * Payment Gateway PayChangu - https://paychangu.com
 *
 * created for SmartNeti
 *
 **/

function paychangu_validate_config()
{
  global $config;
  if (empty($config['paychangu_secret_key']) || empty($config['paychangu_callback_url']) || empty($config['paychangu_webhook_secret'])) {
    sendTelegram("PayChangu payment gateway not configured");
    r2(U . 'order/package', 'w', Lang::T("Admin has not yet setup PayChangu payment gateway, please tell admin"));
  }
}


function paychangu_show_config()
{
  global $ui, $config;
  $ui->assign('_title', 'PayChangu - Payment Gateway - ' . $config['CompanyName']);
  $ui->display('paychangu.tpl');
}


function paychangu_save_config()
{
  global $admin, $_L;
  $secret_key = _post('secret_key');
  $public_key = _post('public_key');
  $callback_url = _post('callback_url');
  $return_url = _post('return_url');
  $currency = _post('currency');
  $webhook_secret = _post('webhook_secret');

  $checkSecretKey = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_secret_key')->find_one();
  if ($checkSecretKey) {
    $checkSecretKey->value = $secret_key;
    $checkSecretKey->save();
  } else {
    $checkSecretKey = ORM::for_table('tbl_appconfig')->create();
    $checkSecretKey->setting = 'paychangu_secret_key';
    $checkSecretKey->value = $secret_key;
    $checkSecretKey->save();
  }

  $checkPublicKey = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_public_key')->find_one();
  if ($checkPublicKey) {
    $checkPublicKey->value = $public_key;
    $checkPublicKey->save();
  } else {
    $checkPublicKey = ORM::for_table('tbl_appconfig')->create();
    $checkPublicKey->setting = 'paychangu_public_key';
    $checkPublicKey->value = $public_key;
    $checkPublicKey->save();
  }

  $checkCallbackUrl = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_callback_url')->find_one();
  if ($checkCallbackUrl) {
    $checkCallbackUrl->value = $callback_url;
    $checkCallbackUrl->save();
  } else {
    $checkCallbackUrl = ORM::for_table('tbl_appconfig')->create();
    $checkCallbackUrl->setting = 'paychangu_callback_url';
    $checkCallbackUrl->value = $callback_url;
    $checkCallbackUrl->save();
  }

  $checkReturnUrl = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_return_url')->find_one();
  if ($checkReturnUrl) {
    $checkReturnUrl->value = $return_url;
    $checkReturnUrl->save();
  } else {
    $checkReturnUrl = ORM::for_table('tbl_appconfig')->create();
    $checkReturnUrl->setting = 'paychangu_return_url';
    $checkReturnUrl->value = $return_url;
    $checkReturnUrl->save();
  }

  $checkCurrency = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_currency')->find_one();
  if ($checkCurrency) {
    $checkCurrency->value = $currency;
    $checkCurrency->save();
  } else {
    $checkCurrency = ORM::for_table('tbl_appconfig')->create();
    $checkCurrency->setting = 'paychangu_currency';
    $checkCurrency->value = $currency;
    $checkCurrency->save();
  }

  $checkWebhookSecret = ORM::for_table('tbl_appconfig')->where('setting', 'paychangu_webhook_secret')->find_one();
  if ($checkWebhookSecret) {
    $checkWebhookSecret->value = $webhook_secret;
    $checkWebhookSecret->save();
  } else {
    $checkWebhookSecret = ORM::for_table('tbl_appconfig')->create();
    $checkWebhookSecret->setting = 'paychangu_webhook_secret';
    $checkWebhookSecret->value = $webhook_secret;
    $checkWebhookSecret->save();
  }

  _log('[' . $admin['username'] . ']: PayChangu ' . $_L['Settings_Saved_Successfully'], 'Admin', $admin['id']);

  r2(U . 'paymentgateway/paychangu', 's', $_L['Settings_Saved_Successfully']);
}


function paychangu_create_transaction($trx, $user)
{
  global $config, $routes;
  
  // Generate unique transaction reference
  $tx_ref = 'INV-' . $trx['id'] . '-' . time();
  
  $url = 'https://api.paychangu.com/payment';
  
  $fields = [
    'amount' => $trx['price'],
    'currency' => $config['paychangu_currency'] ?: 'MWK',
    'email' => $user['email'] ?: '',
    'first_name' => $user['fullname'] ? explode(' ', $user['fullname'])[0] : '',
    'last_name' => $user['fullname'] ? (count(explode(' ', $user['fullname'])) > 1 ? implode(' ', array_slice(explode(' ', $user['fullname']), 1)) : '') : '',
    'callback_url' => $config['paychangu_callback_url'],
    'return_url' => $config['paychangu_return_url'] ?: U . 'order/view/' . $trx['id'],
    'tx_ref' => $tx_ref,
    'customization' => [
      'title' => $config['CompanyName'] . ' - Payment',
      'description' => 'Payment for invoice #' . $trx['id']
    ],
    'meta' => [
      'invoice_id' => $trx['id'],
      'username' => $user['username']
    ]
  ];
  
  $payloadJson = json_encode($fields);
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $payloadJson,
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $config['paychangu_secret_key']
    ]
  ]);
  $response = curl_exec($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  
  $responseData = json_decode($response);
  
  if (isset($responseData->status) && $responseData->status == 'success' && isset($responseData->data->checkout_url)) {
    $checkout_url = $responseData->data->checkout_url;
    
    // Update transaction record
    $d = ORM::for_table('tbl_payment_gateway')
      ->where('username', $user['username'])
      ->where('status', 1)
      ->find_one();
    
    if ($d) {
      $d->gateway_trx_id = $tx_ref;
      $d->pg_url_payment = $checkout_url;
      $d->pg_request = $user['id'];
      $d->expired_date = date('Y-m-d H:i:s', strtotime("+30 minutes"));
      $d->save();
      
      // Redirect to PayChangu checkout
      header("Location: " . $checkout_url);
      exit;
    } else {
      sendTelegram("PayChangu payment failed - Transaction record not found\n\n" . json_encode($responseData, JSON_PRETTY_PRINT));
      r2(U . 'order/package', 'e', Lang::T("Failed to create transaction. Transaction record not found."));
    }
  } else {
    sendTelegram("PayChangu payment failed\n\nResponse: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\nHTTP Code: " . $httpCode);
    r2(U . 'order/package', 'e', Lang::T("Failed to create transaction. " . ($responseData->message ?? 'Unknown error')));
  }
}


function paychangu_payment_notification()
{
  global $config;
  header("Content-Type: application/json");
  
  $webhookData = file_get_contents('php://input');
  $logFile = "PayChanguWebhook.json";
  $log = fopen($logFile, "a");
  fwrite($log, date('Y-m-d H:i:s') . " - " . $webhookData . "\n");
  fclose($log);
  
  // Verify webhook signature
  $headers = getallheaders();
  $signature = isset($headers['Signature']) ? $headers['Signature'] : '';
  
  if (!empty($config['paychangu_webhook_secret'])) {
    $computedSignature = hash_hmac('sha256', $webhookData, $config['paychangu_webhook_secret']);
    if ($computedSignature !== $signature) {
      _log("PayChangu Webhook signature verification failed");
      http_response_code(403);
      echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
      exit;
    }
  }
  
  $webhookContent = json_decode($webhookData);
  
  // Check for tx_ref or reference field
  $tx_ref = $webhookContent->tx_ref ?? $webhookContent->reference ?? '';
  
  if (empty($tx_ref)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid webhook payload - missing tx_ref/reference']);
    exit;
  }
  
  $status = $webhookContent->status ?? '';
  
  // Find transaction by tx_ref
  $trx = ORM::for_table('tbl_payment_gateway')
    ->where('gateway_trx_id', $tx_ref)
    ->find_one();
  
  if (!$trx) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
  }
  
  // Verify transaction with PayChangu API before processing
  $verified = paychangu_verify_transaction($tx_ref);
  
  if ($verified && $status == 'success') {
    $user = ORM::for_table('tbl_customers')
      ->where('username', $trx['username'])
      ->find_one();
    
    if ($user) {
      if (!Package::rechargeUser($user['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
        _log("PayChangu Payment Successful, But Failed to activate Package for user: " . $user['username']);
        sendTelegram("PayChangu Payment activation failed for user: " . $user['username']);
      } else {
        _log("PayChangu Payment Successful for user: " . $user['username']);
      }
    }
    
    $trx->pg_paid_response = json_encode($webhookContent);
    $trx->payment_method = 'PayChangu';
    $trx->payment_channel = $webhookContent->authorization->channel ?? 'PayChangu Checkout';
    $trx->paid_date = date('Y-m-d H:i:s');
    $trx->status = 2;
    $trx->save();
    
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully']);
  } else {
    $trx->status = 1;
    $trx->save();
    http_response_code(200);
    echo json_encode(['status' => 'pending', 'message' => 'Payment not successful']);
  }
}


function paychangu_verify_transaction($tx_ref)
{
  global $config;
  
  $url = 'https://api.paychangu.com/verify-payment/' . $tx_ref;
  
  $curl = curl_init();
  curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $config['paychangu_secret_key']
    ]
  ]);
  $response = curl_exec($curl);
  $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  
  $responseData = json_decode($response);
  
  $logFile = "PayChanguVerify.json";
  $log = fopen($logFile, "a");
  fwrite($log, date('Y-m-d H:i:s') . " - TX_REF: " . $tx_ref . " - Response: " . $response . "\n");
  fclose($log);
  
  if (isset($responseData->status) && $responseData->status == 'success' && isset($responseData->data->status)) {
    return $responseData->data->status == 'success';
  }
  
  return false;
}


function paychangu_get_status($trx, $user)
{
  global $config;
  
  $tx_ref = $trx['gateway_trx_id'];
  $verified = paychangu_verify_transaction($tx_ref);
  
  if ($verified) {
    // Check if already processed
    if ($trx['status'] == 2) {
      r2(U . "order/view/" . $trx['id'], 's', Lang::T("Transaction successful."));
    } else {
      // Process the payment
      $userObj = ORM::for_table('tbl_customers')
        ->where('username', $trx['username'])
        ->find_one();
      
      if ($userObj) {
        if (!Package::rechargeUser($userObj['id'], $trx['routers'], $trx['plan_id'], $trx['gateway'], 'PayChangu')) {
          _log("PayChangu Payment Verification Successful, But Failed to activate Package");
        }
      }
      
      $trx->payment_method = 'PayChangu';
      $trx->paid_date = date('Y-m-d H:i:s');
      $trx->status = 2;
      $trx->save();
      
      r2(U . "order/view/" . $trx['id'], 's', Lang::T("Transaction successful."));
    }
  } else {
    r2(U . "order/view/" . $trx['id'], 'w', Lang::T("Transaction still unpaid."));
  }
}
